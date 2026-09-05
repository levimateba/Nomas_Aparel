<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CashierShiftService
{
    public function currentOpen(?int $userId = null): ?CashierShift
    {
        return CashierShift::query()
            ->where('status', 'open')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->latest('opened_at')
            ->first();
    }

    public function open(float $openingCash = 0, ?string $notes = null): CashierShift
    {
        $userId = auth()->id();

        if ($this->currentOpen($userId)) {
            throw ValidationException::withMessages(['shift' => 'You already have an open shift.']);
        }

        $shift = CashierShift::create([
            'user_id' => $userId,
            'opening_cash' => $openingCash,
            'expected_cash' => $openingCash,
            'opened_at' => now(),
            'status' => 'open',
            'notes' => $notes,
        ]);

        Audit::log(
            'shift_opened',
            'Opened cashier shift with opening cash KES '.number_format($openingCash, 2),
            $shift,
            ['opening_cash' => $openingCash],
            'pos'
        );

        return $shift;
    }

    public function close(CashierShift $shift, float $actualCash, ?string $notes = null): CashierShift
    {
        if (! $shift->isOpen()) {
            throw ValidationException::withMessages(['shift' => 'This shift is already closed.']);
        }

        return DB::transaction(function () use ($shift, $actualCash, $notes) {
            $expected = $this->expectedCash($shift);

            $shift->update([
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'difference' => round($actualCash - $expected, 2),
                'closed_at' => now(),
                'status' => 'closed',
                'notes' => trim(($shift->notes ? $shift->notes."\n" : '').($notes ?? '')),
            ]);

            $closed = $shift->fresh(['user', 'orders']);

            Audit::log(
                'shift_closed',
                'Closed cashier shift. Expected KES '.number_format($expected, 2).', actual KES '.number_format($actualCash, 2).', variance KES '.number_format($actualCash - $expected, 2),
                $closed,
                [
                    'expected' => $expected,
                    'actual' => $actualCash,
                    'variance' => round($actualCash - $expected, 2),
                ],
                'pos'
            );

            return $closed;
        });
    }

    public function expectedCash(CashierShift $shift): float
    {
        $end = $shift->closed_at ?? now();
        $cashSales = (float) Order::query()
            ->where('cashier_shift_id', $shift->id)
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $cashRefunds = 0.0;
        if (Schema::hasTable('order_returns')) {
            $cashRefunds = (float) OrderReturn::query()
                ->where('user_id', $shift->user_id)
                ->where('refund_method', 'cash')
                ->whereBetween('created_at', [$shift->opened_at, $end])
                ->sum('total');
        }

        return round((float) $shift->opening_cash + $cashSales - $cashRefunds, 2);
    }

    public function performance(CashierShift $shift): array
    {
        $sales = Order::query()->where('cashier_shift_id', $shift->id)->where('status', '!=', 'cancelled');
        $end = $shift->closed_at ?? now();
        $byMethod = Order::query()
            ->selectRaw('payment_method, count(*) as count, sum(total_amount) as total')
            ->where('cashier_shift_id', $shift->id)
            ->where('status', '!=', 'cancelled')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $returns = 0.0;
        if (Schema::hasTable('order_returns')) {
            $returns = (float) OrderReturn::query()
                ->where('user_id', $shift->user_id)
                ->whereBetween('created_at', [$shift->opened_at, $end])
                ->sum('total');
        }

        return [
            'transactions' => (clone $sales)->count(),
            'net_sales' => (float) (clone $sales)->sum('total_amount'),
            'returns' => $returns,
            'cash' => (float) ($byMethod['cash'] ?? 0),
            'mobile_money' => (float) ($byMethod['mobile_money'] ?? 0),
            'card' => (float) ($byMethod['card'] ?? 0),
            'bank' => (float) ($byMethod['bank_transfer'] ?? 0),
            'opening_cash' => (float) $shift->opening_cash,
            'expected_cash' => $shift->status === 'closed' ? (float) $shift->expected_cash : $this->expectedCash($shift),
            'actual_cash' => $shift->actual_cash !== null ? (float) $shift->actual_cash : null,
            'variance' => $shift->difference !== null ? (float) $shift->difference : null,
        ];
    }
}
