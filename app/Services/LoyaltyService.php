<?php

namespace App\Services;

use App\Models\LoyaltyCard;
use App\Models\LoyaltySetting;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ShopCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    public function settings(): LoyaltySetting
    {
        return LoyaltySetting::current();
    }

    public function getEligibleAmount(float $subtotal, float $discount, float $loyaltyDiscount = 0): float
    {
        $settings = $this->settings();
        $amount = $settings->allow_earn_on_discounted
            ? max($subtotal - $loyaltyDiscount, 0)
            : max($subtotal - $discount - $loyaltyDiscount, 0);

        return round($amount, 2);
    }

    public function calculateEarnedPoints(float $eligibleAmount): int
    {
        $settings = $this->settings();
        if (! $settings->enabled) {
            return 0;
        }

        $amountPerPoint = (float) $settings->amount_per_point;
        $pointsAwarded = (int) $settings->points_awarded;
        $minimum = (float) $settings->minimum_purchase;

        if ($amountPerPoint <= 0 || $pointsAwarded <= 0) {
            return 0;
        }

        if ($eligibleAmount < $minimum) {
            return 0;
        }

        return (int) floor($eligibleAmount / $amountPerPoint) * $pointsAwarded;
    }

    public function getRedemptionValue(int $points): float
    {
        $settings = $this->settings();
        $required = max(1, (int) $settings->redemption_points);
        $value = (float) $settings->redemption_value;

        if ($points < $required || $value < 0) {
            return 0.0;
        }

        $blocks = (int) floor($points / $required);

        return round($blocks * $value, 2);
    }

    public function getCustomerBalance(ShopCustomer $customer): int
    {
        $card = $this->activeCard($customer);

        return $card ? (int) $card->points_balance : 0;
    }

    public function activeCard(ShopCustomer $customer): ?LoyaltyCard
    {
        if (! Schema::hasTable('loyalty_cards')) {
            return null;
        }

        return LoyaltyCard::query()
            ->where('shop_customer_id', $customer->id)
            ->where('status', LoyaltyCard::STATUS_ACTIVE)
            ->first();
    }

    public function createLoyaltyCard(ShopCustomer $customer): LoyaltyCard
    {
        if (! $this->settings()->enabled) {
            throw ValidationException::withMessages([
                'loyalty' => 'Loyalty program is disabled.',
            ]);
        }

        return DB::transaction(function () use ($customer) {
            $existing = LoyaltyCard::query()
                ->where('shop_customer_id', $customer->id)
                ->where('status', LoyaltyCard::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'loyalty' => 'This customer already has an active loyalty card ('.$existing->card_number.').',
                ]);
            }

            return LoyaltyCard::query()->create([
                'shop_customer_id' => $customer->id,
                'card_number' => $this->nextCardNumber(),
                'points_balance' => 0,
                'status' => LoyaltyCard::STATUS_ACTIVE,
                'issued_at' => now(),
            ]);
        });
    }

    public function nextCardNumber(): string
    {
        $last = LoyaltyCard::query()->orderByDesc('id')->value('card_number');
        $seq = 1;
        if (is_string($last) && preg_match('/NOM-(\d+)/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'NOM-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function earnPoints(ShopCustomer $customer, Order $order, int $points, ?int $userId = null): ?LoyaltyTransaction
    {
        $settings = $this->settings();
        if (! $settings->enabled || $points <= 0) {
            return null;
        }

        return $this->applyDelta(
            $customer,
            $points,
            LoyaltyTransaction::TYPE_EARNED,
            'Earned '.$points.' loyalty points from sale '.$order->order_number,
            $userId,
            $order->id
        );
    }

    public function redeemPoints(ShopCustomer $customer, Order $order, int $points, float $discountValue, ?int $userId = null): ?LoyaltyTransaction
    {
        $settings = $this->settings();
        if (! $settings->enabled || ! $settings->redemption_enabled || $points <= 0) {
            return null;
        }

        if ($points < (int) $settings->redemption_points) {
            throw ValidationException::withMessages([
                'loyalty' => 'Minimum redemption is '.$settings->redemption_points.' points.',
            ]);
        }

        return $this->applyDelta(
            $customer,
            -$points,
            LoyaltyTransaction::TYPE_REDEEMED,
            'Redeemed '.$points.' loyalty points for KES '.number_format($discountValue, 2).' discount',
            $userId,
            $order->id
        );
    }

    public function reversePointsForReturn(Order $order, OrderReturn $return, float $refundAmount): ?LoyaltyTransaction
    {
        $settings = $this->settings();
        if (! $settings->enabled || ! $settings->reverse_points_on_refund) {
            return null;
        }

        if (! Schema::hasColumn('orders', 'shop_customer_id') || ! $order->shop_customer_id) {
            return null;
        }

        $customer = ShopCustomer::query()->find($order->shop_customer_id);
        if (! $customer) {
            return null;
        }

        $earned = (int) ($order->loyalty_points_earned ?? 0);
        if ($earned <= 0) {
            return null;
        }

        $orderTotal = (float) $order->total_amount;
        if ($orderTotal <= 0) {
            return null;
        }

        $alreadyReversed = (int) LoyaltyTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', LoyaltyTransaction::TYPE_REVERSED)
            ->sum(DB::raw('ABS(points)'));

        $remaining = max(0, $earned - $alreadyReversed);
        if ($remaining <= 0) {
            return null;
        }

        $ratio = min(1, max(0, $refundAmount / $orderTotal));
        $toReverse = (int) max(1, (int) floor($earned * $ratio));
        $toReverse = min($toReverse, $remaining);

        if ($toReverse <= 0) {
            return null;
        }

        try {
            return $this->applyDelta(
                $customer,
                -$toReverse,
                LoyaltyTransaction::TYPE_REVERSED,
                'Reversed '.$toReverse.' points for return '.$return->return_number.' on '.$order->order_number,
                auth()->id(),
                $order->id,
                $return->id,
                allowNegativeClamp: true
            );
        } catch (ValidationException $e) {
            // If customer already spent points, clamp reverse to available balance.
            $balance = $this->getCustomerBalance($customer);
            if ($balance <= 0) {
                return null;
            }

            $clamped = min($toReverse, $balance);

            return $this->applyDelta(
                $customer,
                -$clamped,
                LoyaltyTransaction::TYPE_REVERSED,
                'Reversed '.$clamped.' of '.$toReverse.' points for return '.$return->return_number.' (balance limited)',
                auth()->id(),
                $order->id,
                $return->id,
                allowNegativeClamp: true
            );
        }
    }

    public function adjustPoints(ShopCustomer $customer, int $points, string $reason, ?int $userId = null): LoyaltyTransaction
    {
        if ($points === 0) {
            throw ValidationException::withMessages(['points' => 'Adjustment points cannot be zero.']);
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'A reason is required.']);
        }

        return $this->applyDelta(
            $customer,
            $points,
            LoyaltyTransaction::TYPE_ADJUSTMENT,
            $reason,
            $userId
        );
    }

    public function blockCard(LoyaltyCard $card, ?int $userId = null): LoyaltyCard
    {
        return DB::transaction(function () use ($card, $userId) {
            $locked = LoyaltyCard::query()->lockForUpdate()->findOrFail($card->id);
            $locked->update(['status' => LoyaltyCard::STATUS_BLOCKED]);

            return $locked->fresh();
        });
    }

    /**
     * @param  bool  $allowNegativeClamp  When reversing, never go below zero — throw if insufficient unless caller clamps.
     */
    private function applyDelta(
        ShopCustomer $customer,
        int $pointsDelta,
        string $type,
        string $description,
        ?int $userId = null,
        ?int $orderId = null,
        ?int $orderReturnId = null,
        bool $allowNegativeClamp = false
    ): LoyaltyTransaction {
        return DB::transaction(function () use ($customer, $pointsDelta, $type, $description, $userId, $orderId, $orderReturnId, $allowNegativeClamp) {
            $card = LoyaltyCard::query()
                ->where('shop_customer_id', $customer->id)
                ->where('status', LoyaltyCard::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $card) {
                throw ValidationException::withMessages([
                    'loyalty' => 'Customer has no active loyalty card.',
                ]);
            }

            $before = (int) $card->points_balance;
            $after = $before + $pointsDelta;

            if ($after < 0) {
                if ($allowNegativeClamp) {
                    throw ValidationException::withMessages([
                        'loyalty' => 'Insufficient loyalty points to reverse (balance '.$before.').',
                    ]);
                }

                throw ValidationException::withMessages([
                    'loyalty' => 'Insufficient loyalty points. Available: '.$before.'.',
                ]);
            }

            $card->forceFill(['points_balance' => $after])->save();

            return LoyaltyTransaction::query()->create([
                'shop_customer_id' => $customer->id,
                'loyalty_card_id' => $card->id,
                'order_id' => $orderId,
                'order_return_id' => $orderReturnId,
                'type' => $type,
                'points' => $pointsDelta,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
                'created_by' => $userId,
            ]);
        });
    }
}
