<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyCard;
use App\Models\LoyaltySetting;
use App\Models\LoyaltyTransaction;
use App\Models\ShopCustomer;
use App\Services\LoyaltyService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function __construct(private readonly LoyaltyService $loyalty)
    {
    }

    public function settings(): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty'), 403);

        $settings = $this->loyalty->settings();

        return view('admin.loyalty.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty'), 403);

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'amount_per_point' => ['required', 'numeric', 'min:0.01'],
            'points_awarded' => ['required', 'integer', 'min:1'],
            'minimum_purchase' => ['required', 'numeric', 'min:0'],
            'redemption_enabled' => ['nullable', 'boolean'],
            'redemption_points' => ['required', 'integer', 'min:1'],
            'redemption_value' => ['required', 'numeric', 'min:0'],
            'allow_earn_on_discounted' => ['nullable', 'boolean'],
            'allow_redemption_at_pos' => ['nullable', 'boolean'],
            'show_estimated_points_on_pos' => ['nullable', 'boolean'],
            'show_balance_after_sale' => ['nullable', 'boolean'],
            'show_on_receipt' => ['nullable', 'boolean'],
            'reverse_points_on_refund' => ['nullable', 'boolean'],
            'points_expiration_enabled' => ['nullable', 'boolean'],
            'points_expiration_days' => ['required', 'integer', 'min:1'],
        ]);

        $settings = LoyaltySetting::current();
        $settings->fill([
            'enabled' => $request->boolean('enabled'),
            'amount_per_point' => $data['amount_per_point'],
            'points_awarded' => $data['points_awarded'],
            'minimum_purchase' => $data['minimum_purchase'],
            'redemption_enabled' => $request->boolean('redemption_enabled'),
            'redemption_points' => $data['redemption_points'],
            'redemption_value' => $data['redemption_value'],
            'allow_earn_on_discounted' => $request->boolean('allow_earn_on_discounted'),
            'allow_redemption_at_pos' => $request->boolean('allow_redemption_at_pos'),
            'show_estimated_points_on_pos' => $request->boolean('show_estimated_points_on_pos'),
            'show_balance_after_sale' => $request->boolean('show_balance_after_sale'),
            'show_on_receipt' => $request->boolean('show_on_receipt'),
            'reverse_points_on_refund' => $request->boolean('reverse_points_on_refund'),
            'points_expiration_enabled' => $request->boolean('points_expiration_enabled'),
            'points_expiration_days' => $data['points_expiration_days'],
        ])->save();

        Audit::log('loyalty_settings_updated', 'Updated customer loyalty settings', $settings, [], 'loyalty');

        return back()->with('success', 'Loyalty settings saved.');
    }

    public function dashboard(): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty') || auth()->user()?->hasPermission('manage_customers'), 403);

        $members = LoyaltyCard::query()->where('status', LoyaltyCard::STATUS_ACTIVE)->count();
        $activeCards = $members;
        $pointsIssued = (int) LoyaltyTransaction::query()->where('type', LoyaltyTransaction::TYPE_EARNED)->sum('points');
        $pointsRedeemed = (int) abs((int) LoyaltyTransaction::query()->where('type', LoyaltyTransaction::TYPE_REDEEMED)->sum('points'));

        $topCustomers = LoyaltyCard::query()
            ->with('customer')
            ->where('status', LoyaltyCard::STATUS_ACTIVE)
            ->orderByDesc('points_balance')
            ->limit(10)
            ->get()
            ->map(function (LoyaltyCard $card) {
                $spent = $card->customer
                    ? (float) $card->customer->orders()->where('status', '!=', 'cancelled')->sum('total_amount')
                    : 0;

                return [
                    'customer' => $card->customer?->name ?? '—',
                    'points' => (int) $card->points_balance,
                    'spent' => $spent,
                    'card' => $card->card_number,
                ];
            });

        return view('admin.loyalty.dashboard', compact(
            'members',
            'activeCards',
            'pointsIssued',
            'pointsRedeemed',
            'topCustomers'
        ));
    }

    public function history(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty') || auth()->user()?->hasPermission('manage_customers'), 403);

        $transactions = LoyaltyTransaction::query()
            ->with(['customer', 'card', 'order', 'creator'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $q->where(function ($inner) use ($term) {
                    $inner->where('description', 'like', "%{$term}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"))
                        ->orWhereHas('card', fn ($c) => $c->where('card_number', 'like', "%{$term}%"))
                        ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('shop_customer_id', (int) $request->input('customer_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $customers = ShopCustomer::query()->orderBy('name')->limit(200)->get(['id', 'name']);

        $kpis = [
            'members' => LoyaltyCard::query()->where('status', LoyaltyCard::STATUS_ACTIVE)->count(),
            'issued' => (int) LoyaltyTransaction::query()->where('type', LoyaltyTransaction::TYPE_EARNED)->sum('points'),
            'redeemed' => (int) abs((int) LoyaltyTransaction::query()->where('type', LoyaltyTransaction::TYPE_REDEEMED)->sum('points')),
            'active' => LoyaltyCard::query()->where('status', LoyaltyCard::STATUS_ACTIVE)->count(),
        ];

        return view('admin.loyalty.history', compact('transactions', 'customers', 'kpis'));
    }

    public function issueCard(ShopCustomer $customer): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty') || auth()->user()?->hasPermission('manage_customers'), 403);

        $card = $this->loyalty->createLoyaltyCard($customer);
        Audit::log('loyalty_card_issued', 'Issued loyalty card '.$card->card_number, $customer, [], 'loyalty');

        return back()->with('success', 'Loyalty card '.$card->card_number.' issued.');
    }

    public function adjust(Request $request, ShopCustomer $customer): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty'), 403);

        $data = $request->validate([
            'direction' => ['required', 'in:add,remove'],
            'points' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $delta = $data['direction'] === 'add' ? (int) $data['points'] : -((int) $data['points']);
        $tx = $this->loyalty->adjustPoints($customer, $delta, $data['reason'], auth()->id());

        Audit::log('loyalty_points_adjusted', $data['reason'], $customer, [
            'points' => $delta,
            'balance_after' => $tx->balance_after,
        ], 'loyalty');

        return back()->with('success', 'Loyalty points updated.');
    }

    public function blockCard(LoyaltyCard $card): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_loyalty'), 403);

        $this->loyalty->blockCard($card, auth()->id());
        Audit::log('loyalty_card_blocked', 'Blocked loyalty card '.$card->card_number, $card->customer, [], 'loyalty');

        return back()->with('success', 'Loyalty card blocked.');
    }
}
