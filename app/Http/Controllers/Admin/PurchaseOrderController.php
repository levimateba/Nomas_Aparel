<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_purchase_orders')
            || auth()->user()?->hasPermission('manage_purchases'),
            403
        );

        $orders = PurchaseOrder::query()
            ->with('supplier')
            ->latest('order_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.purchase-orders.index', compact('orders'));
    }

    public function create(): View
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_purchase_orders')
            || auth()->user()?->hasPermission('manage_purchases'),
            403
        );

        return view('admin.purchase-orders.create', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'barcode', 'stock', 'buying_price', 'reorder_level']),
        ]);
    }

    public function store(Request $request, PurchaseOrderService $orders): RedirectResponse
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_purchase_orders')
            || auth()->user()?->hasPermission('manage_purchases'),
            403
        );

        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.buying_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = $orders->create($data);

        return redirect()->route('admin.purchase-orders.show', $order)->with('success', 'Purchase order created: '.$order->po_number);
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_purchase_orders')
            || auth()->user()?->hasPermission('manage_purchases'),
            403
        );
        $purchaseOrder->load(['items.product', 'supplier', 'user', 'approver', 'receipts']);

        return view('admin.purchase-orders.show', ['order' => $purchaseOrder]);
    }

    public function submit(PurchaseOrder $purchaseOrder, PurchaseOrderService $orders): RedirectResponse
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_purchase_orders')
            || auth()->user()?->hasPermission('manage_purchases'),
            403
        );
        $orders->submit($purchaseOrder);

        return back()->with('success', 'Purchase order submitted.');
    }

    public function approve(PurchaseOrder $purchaseOrder, PurchaseOrderService $orders): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('approve_purchase_orders'), 403);
        $orders->approve($purchaseOrder);

        return back()->with('success', 'Purchase order approved.');
    }

    public function cancel(PurchaseOrder $purchaseOrder, PurchaseOrderService $orders): RedirectResponse
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_purchase_orders')
            || auth()->user()?->hasPermission('manage_purchases'),
            403
        );
        $orders->cancel($purchaseOrder);

        return back()->with('success', 'Purchase order cancelled.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderService $orders): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_purchases'), 403);

        $data = $request->validate([
            'purchase_date' => ['nullable', 'date'],
            'invoice_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_status' => ['nullable', 'in:unpaid,partial,paid'],
            'items' => ['required', 'array'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.buying_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $orders->receive($purchaseOrder, $data);

        return back()->with('success', 'Stock received against purchase order.');
    }
}
