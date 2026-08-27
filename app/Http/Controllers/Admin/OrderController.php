<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function index()
    {
        $query = Order::query()->with('user')->latest();
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('payment_method')) {
            $query->where('payment_method', request('payment_method'));
        }
        if (request()->filled('source') && Schema::hasColumn('orders', 'source')) {
            $query->where('source', request('source'));
        }
        if (request()->filled('q')) {
            $term = trim((string) request('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('order_number', 'like', '%' . $term . '%')
                    ->orWhere('customer_name', 'like', '%' . $term . '%')
                    ->orWhere('customer_email', 'like', '%' . $term . '%');
            });
        }
        if (request()->filled('from_date')) {
            $query->whereDate('created_at', '>=', request('from_date'));
        }
        if (request()->filled('to_date')) {
            $query->whereDate('created_at', '<=', request('to_date'));
        }
        $orders = $query->paginate(20)->withQueryString();

        $todayStart = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $summaries = [
            'today' => [
                'count' => Order::where('created_at', '>=', $todayStart)->count(),
                'revenue' => (float) Order::where('created_at', '>=', $todayStart)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
            ],
            'week' => [
                'count' => Order::where('created_at', '>=', $weekStart)->count(),
                'revenue' => (float) Order::where('created_at', '>=', $weekStart)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
            ],
            'month' => [
                'count' => Order::where('created_at', '>=', $monthStart)->count(),
                'revenue' => (float) Order::where('created_at', '>=', $monthStart)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
            ],
        ];

        return view('admin.orders.index', compact('orders', 'summaries'));
    }

    public function show(Order $order)
    {
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load('items');
        $settings = Setting::get_settings();

        return view('admin.orders.invoice', [
            'order' => $order,
            'settings' => $settings,
            'generatedAt' => now(),
        ]);
    }

    public function downloadPdf(Order $order)
    {
        $order->load('items');
        $settings = Setting::get_settings();

        $pdf = Pdf::loadView('admin.orders.invoice-pdf', [
            'order' => $order,
            'settings' => $settings,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('order-' . $order->order_number . '.pdf');
    }

    public function sendConfirmation(Order $order)
    {
        $order->load('items');

        try {
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
        } catch (\Throwable $exception) {
            return back()->withErrors(['email' => 'Could not send email right now.']);
        }

        return back()->with('success', 'Order confirmation email sent.');
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,processing,paid,shipped,delivered,cancelled,cancellation_requested',
        ]);

        $order->update($data);

        return back()->with('success', 'Order status updated.');
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|in:pending,processing,paid,shipped,delivered,cancelled,cancellation_requested',
        ]);

        Order::query()->whereIn('id', $data['order_ids'])->update(['status' => $data['status']]);

        return back()->with('success', 'Selected orders updated.');
    }

    public function exportCsv(Request $request)
    {
        $query = Order::query()->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method')->toString());
        }
        if ($request->filled('source') && Schema::hasColumn('orders', 'source')) {
            $query->where('source', $request->string('source')->toString());
        }
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($term) {
                $builder->where('order_number', 'like', '%' . $term . '%')
                    ->orWhere('customer_name', 'like', '%' . $term . '%')
                    ->orWhere('customer_email', 'like', '%' . $term . '%');
            });
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->string('from_date')->toString());
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->string('to_date')->toString());
        }

        $rows = $query->get();
        $filename = 'orders-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Order Number', 'Customer', 'Email', 'Phone', 'Payment', 'Source', 'Status', 'Total', 'Date']);
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->id,
                    $row->order_number,
                    $row->customer_name,
                    $row->customer_email,
                    $row->customer_phone,
                    $row->payment_method,
                    $row->source ?? (str_starts_with((string) $row->order_number, 'POS-') ? 'pos' : 'online'),
                    $row->status,
                    $row->total_amount,
                    $row->created_at?->toDateTimeString(),
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
