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
        $user = auth()->user();
        if ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier()) {
            $query->where('user_id', $user->id);
        }
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
                $builder->where('order_number', 'like', '%'.$term.'%')
                    ->orWhere('customer_name', 'like', '%'.$term.'%')
                    ->orWhere('customer_email', 'like', '%'.$term.'%');
                if (Schema::hasColumn('orders', 'customer_phone')) {
                    $builder->orWhere('customer_phone', 'like', '%'.$term.'%');
                }
            });
        }
        if (request()->filled('from_date')) {
            $query->whereDate('created_at', '>=', request('from_date'));
        }
        if (request()->filled('to_date')) {
            $query->whereDate('created_at', '<=', request('to_date'));
        }

        $perPage = (int) request('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }
        $orders = $query->paginate($perPage)->withQueryString();

        $summaryBase = Order::query();
        if ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier()) {
            $summaryBase->where('user_id', $user->id);
        }

        $paid = fn ($q) => $q->where('status', '!=', 'cancelled');

        $todayCount = (clone $summaryBase)->whereDate('created_at', today())->count();
        $todayRevenue = (float) $paid(clone $summaryBase)->whereDate('created_at', today())->sum('total_amount');
        $yesterdayCount = (clone $summaryBase)->whereDate('created_at', today()->subDay())->count();
        $yesterdayRevenue = (float) $paid(clone $summaryBase)->whereDate('created_at', today()->subDay())->sum('total_amount');

        $weekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $weekCount = (clone $summaryBase)->where('created_at', '>=', $weekStart)->count();
        $weekRevenue = (float) $paid(clone $summaryBase)->where('created_at', '>=', $weekStart)->sum('total_amount');
        $lastWeekCount = (clone $summaryBase)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $lastWeekRevenue = (float) $paid(clone $summaryBase)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->sum('total_amount');

        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $monthCount = (clone $summaryBase)->where('created_at', '>=', $monthStart)->count();
        $monthRevenue = (float) $paid(clone $summaryBase)->where('created_at', '>=', $monthStart)->sum('total_amount');
        $lastMonthCount = (clone $summaryBase)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthRevenue = (float) $paid(clone $summaryBase)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');

        $allCount = (clone $summaryBase)->count();
        $allRevenue = (float) $paid(clone $summaryBase)->sum('total_amount');

        $trend = function (float $current, float $previous): ?float {
            if ($previous <= 0) {
                return $current > 0 ? 100.0 : null;
            }

            return round((($current - $previous) / $previous) * 100, 0);
        };

        $summaries = [
            'today' => [
                'count' => $todayCount,
                'revenue' => $todayRevenue,
                'trend' => $trend($todayRevenue, $yesterdayRevenue) ?? $trend((float) $todayCount, (float) $yesterdayCount),
            ],
            'week' => [
                'count' => $weekCount,
                'revenue' => $weekRevenue,
                'trend' => $trend($weekRevenue, $lastWeekRevenue) ?? $trend((float) $weekCount, (float) $lastWeekCount),
            ],
            'month' => [
                'count' => $monthCount,
                'revenue' => $monthRevenue,
                'trend' => $trend($monthRevenue, $lastMonthRevenue) ?? $trend((float) $monthCount, (float) $lastMonthCount),
            ],
            'all' => [
                'count' => $allCount,
                'revenue' => $allRevenue,
            ],
        ];

        return view('admin.orders.index', compact('orders', 'summaries', 'perPage'));
    }

    public function show(Order $order)
    {
        $this->assertCashierOwnsOrder($order);
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function print(Order $order)
    {
        $this->assertCashierOwnsOrder($order);
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
        $this->assertCashierOwnsOrder($order);
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
        $this->assertCashierOwnsOrder($order);
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
        abort_if($this->isFrontlineCashier(), 403, 'Cashiers cannot change order status.');

        $data = $request->validate([
            'status' => 'required|in:pending,processing,paid,shipped,delivered,cancelled,cancellation_requested,return_requested',
        ]);

        $order->update($data);

        return back()->with('success', 'Order status updated.');
    }

    public function bulkUpdate(Request $request)
    {
        abort_if($this->isFrontlineCashier(), 403, 'Cashiers cannot bulk-update orders.');

        $data = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|in:pending,processing,paid,shipped,delivered,cancelled,cancellation_requested,return_requested',
        ]);

        Order::query()->whereIn('id', $data['order_ids'])->update(['status' => $data['status']]);

        return back()->with('success', 'Selected orders updated.');
    }

    public function exportCsv(Request $request)
    {
        $query = Order::query()->latest();
        $user = auth()->user();
        if ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier()) {
            $query->where('user_id', $user->id);
        }
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

    private function isFrontlineCashier(): bool
    {
        $user = auth()->user();

        return (bool) ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier());
    }

    private function assertCashierOwnsOrder(Order $order): void
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier() && (int) $order->user_id !== (int) $user->id) {
            abort(403, 'You can only view your own sales.');
        }
    }
}
