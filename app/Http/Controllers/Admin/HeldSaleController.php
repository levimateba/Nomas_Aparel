<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeldSale;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeldSaleController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission('create_sale'), 403);

        $holds = HeldSale::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('admin.holds.index', compact('holds'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('create_sale'), 403);

        $cart = session('pos_cart', []);
        if (empty($cart)) {
            return back()->withErrors(['hold' => 'Cart is empty — nothing to hold.']);
        }

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $hold = HeldSale::create([
            'user_id' => auth()->id(),
            'label' => $data['label'] ?: ('Hold '.now()->format('H:i')),
            'payload' => [
                'cart' => $cart,
                'coupon' => session('pos_coupon'),
            ],
        ]);

        session()->forget(['pos_cart', 'pos_coupon']);
        Audit::log('sale_held', 'Held sale '.$hold->label, $hold, [], 'pos');

        return redirect()->route('admin.pos.index')->with('success', 'Sale held. Cart cleared for a new sale.');
    }

    public function resume(HeldSale $hold): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('create_sale'), 403);
        abort_unless((int) $hold->user_id === (int) auth()->id(), 403);

        $payload = $hold->payload ?? [];
        session([
            'pos_cart' => $payload['cart'] ?? [],
            'pos_coupon' => $payload['coupon'] ?? null,
        ]);
        $hold->delete();
        Audit::log('sale_resumed', 'Resumed held sale', null, [], 'pos');

        return redirect()->route('admin.pos.index')->with('success', 'Held sale restored to cart.');
    }

    public function destroy(HeldSale $hold): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('create_sale'), 403);
        abort_unless((int) $hold->user_id === (int) auth()->id() || auth()->user()?->isFullAdmin(), 403);
        $hold->delete();

        return back()->with('success', 'Held sale discarded.');
    }
}
