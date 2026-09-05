<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_purchases'), 403);

        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_purchases'), 403);

        return view('admin.purchases.create', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'barcode', 'stock', 'buying_price']),
            'locations' => \App\Models\StockLocation::orderedActive(),
        ]);
    }

    public function store(Request $request, PurchaseService $purchases): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_purchases'), 403);

        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock_location_id' => ['required', 'exists:stock_locations,id'],
            'purchase_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'invoice_reference' => ['nullable', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.buying_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $purchase = $purchases->create($data);

        return redirect()->route('admin.purchases.show', $purchase)->with('success', 'Stock received: '.$purchase->purchase_number);
    }

    public function show(Purchase $purchase): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_purchases'), 403);
        $purchase->load(['items.product', 'supplier', 'user', 'payments.user', 'purchaseOrder']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function payment(Request $request, Purchase $purchase, PurchaseService $purchases): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_purchases'), 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:40'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $purchases->recordPayment($purchase, $data);

        return back()->with('success', 'Payment recorded.');
    }
}
