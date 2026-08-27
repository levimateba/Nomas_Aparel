<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\OrderReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index()
    {
        return view('admin.returns.index', [
            'returns' => OrderReturn::with(['order', 'user'])->latest()->paginate(20),
        ]);
    }

    public function create(Request $request)
    {
        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::with('items')->findOrFail($request->query('order_id'));
        } elseif ($request->filled('receipt')) {
            $receipt = trim((string) $request->query('receipt'));
            $order = Order::with('items')
                ->where(function ($query) use ($receipt) {
                    $query->where('order_number', $receipt)
                        ->orWhere('order_number', 'like', '%'.$receipt.'%');
                })
                ->first();
        }

        return view('admin.returns.create', compact('order'));
    }

    public function store(Request $request, OrderReturnService $returns)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'reason' => ['required', 'string', 'max:1000'],
            'refund_method' => ['required', 'in:cash,original,mobile_money'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $order = Order::findOrFail($data['order_id']);
        $return = $returns->process($order, $data);

        return redirect()->route('admin.returns.show', $return)->with('success', 'Return processed and stock restored.');
    }

    public function show(OrderReturn $orderReturn)
    {
        return view('admin.returns.show', [
            'return' => $orderReturn->load(['items.orderItem', 'items.product', 'order.items', 'user']),
        ]);
    }
}
