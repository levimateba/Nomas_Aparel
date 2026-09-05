<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\OrderReturnService;
use App\Support\Audit;
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
            if ($receipt !== '') {
                $order = Order::with('items')
                    ->where(function ($query) use ($receipt) {
                        $query->where('order_number', $receipt)
                            ->orWhere('order_number', 'like', '%'.$receipt.'%');
                    })
                    ->latest()
                    ->first();
            }
        }

        $recentSales = Order::query()
            ->where(function ($q) {
                $q->where('source', 'pos')
                    ->orWhere('order_number', 'like', 'POS-%');
            })
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->latest()
            ->limit(8)
            ->get(['id', 'order_number', 'customer_name', 'total_amount', 'created_at', 'status', 'source']);

        return view('admin.returns.create', compact('order', 'recentSales'));
    }

    public function store(Request $request, OrderReturnService $returns)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'reason' => ['required', 'string', 'max:1000'],
            'refund_method' => ['required', 'in:cash,original,mobile_money'],
            'stock_location_id' => ['nullable', 'exists:stock_locations,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $order = Order::findOrFail($data['order_id']);
        $return = $returns->process($order, $data);

        Audit::log(
            'sale_return',
            'Return on '.$order->order_number.' for KES '.number_format((float) $return->total, 2),
            $return,
            [
                'order_number' => $order->order_number,
                'refund_method' => $data['refund_method'],
                'total' => (float) $return->total,
            ],
            'pos'
        );

        return redirect()->route('admin.returns.show', $return)->with('success', 'Return processed and stock restored.');
    }

    public function show(OrderReturn $orderReturn)
    {
        return view('admin.returns.show', [
            'return' => $orderReturn->load(['items.orderItem', 'items.product', 'order.items', 'user']),
        ]);
    }
}
