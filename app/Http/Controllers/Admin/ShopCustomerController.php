<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyTransaction;
use App\Models\ShopCustomer;
use App\Services\LoyaltyService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopCustomerController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_customers'), 403);

        $customers = ShopCustomer::query()
            ->with('loyaltyCard')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($request->input('loyalty') === 'members', fn ($q) => $q->whereHas('loyaltyCard'))
            ->when($request->input('loyalty') === 'non_members', fn ($q) => $q->whereDoesntHave('loyaltyCard'))
            ->when($request->input('loyalty') === 'active', fn ($q) => $q->whereHas('loyaltyCard', fn ($c) => $c->where('status', 'active')))
            ->when($request->input('loyalty') === 'blocked', fn ($q) => $q->whereHas('loyaltyCards', fn ($c) => $c->where('status', 'blocked')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $loyaltyEnabled = app(LoyaltyService::class)->settings()->enabled;

        return view('admin.customers.index', compact('customers', 'loyaltyEnabled'));
    }

    public function show(ShopCustomer $customer): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_customers'), 403);

        $customer->load(['loyaltyCard', 'loyaltyCards']);
        $loyalty = app(LoyaltyService::class);
        $card = $customer->loyaltyCard;
        $stats = [
            'earned' => (int) LoyaltyTransaction::query()
                ->where('shop_customer_id', $customer->id)
                ->where('type', LoyaltyTransaction::TYPE_EARNED)
                ->sum('points'),
            'redeemed' => (int) abs((int) LoyaltyTransaction::query()
                ->where('shop_customer_id', $customer->id)
                ->where('type', LoyaltyTransaction::TYPE_REDEEMED)
                ->sum('points')),
            'transactions' => LoyaltyTransaction::query()->where('shop_customer_id', $customer->id)->count(),
        ];
        $recent = LoyaltyTransaction::query()
            ->with(['order', 'creator'])
            ->where('shop_customer_id', $customer->id)
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.customers.show', [
            'customer' => $customer,
            'card' => $card,
            'stats' => $stats,
            'recent' => $recent,
            'loyaltyEnabled' => $loyalty->settings()->enabled,
            'canManageLoyalty' => (bool) auth()->user()?->hasPermission('manage_loyalty'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_customers'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'issue_loyalty_card' => ['nullable', 'boolean'],
        ]);

        $customer = ShopCustomer::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);
        Audit::log('customer_created', 'Created customer '.$customer->name, $customer, [], 'customers');

        if ($request->boolean('issue_loyalty_card') && app(LoyaltyService::class)->settings()->enabled) {
            try {
                app(LoyaltyService::class)->createLoyaltyCard($customer);
            } catch (\Throwable $e) {
                return redirect()
                    ->route('admin.customers.show', $customer)
                    ->with('success', 'Customer saved.')
                    ->withErrors(['loyalty' => $e->getMessage()]);
            }
        }

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer saved.');
    }

    public function update(Request $request, ShopCustomer $customer): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_customers'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer->update([
            ...$data,
            'is_active' => $request->boolean('is_active', $customer->is_active),
        ]);

        Audit::log('customer_updated', 'Updated customer '.$customer->name, $customer, [], 'customers');

        return back()->with('success', 'Customer updated.');
    }

    public function destroy(ShopCustomer $customer): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_customers'), 403);
        $name = $customer->name;
        $customer->delete();
        Audit::log('customer_deleted', 'Deleted customer '.$name, null, ['name' => $name], 'customers');

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }
}
