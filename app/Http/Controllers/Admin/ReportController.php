<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\Vendor;
use App\Models\VendorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $ordersQuery = Order::query();
        if (!empty($fromDate)) {
            $ordersQuery->whereDate('created_at', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $ordersQuery->whereDate('created_at', '<=', $toDate);
        }

        $totalOrders = (clone $ordersQuery)->count();
        $totalRevenue = (float) (clone $ordersQuery)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / max($totalOrders, 1) : 0;

        $statusBreakdown = (clone $ordersQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $topProductsQuery = OrderItem::query()
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_sales')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id');
        if (!empty($fromDate)) {
            $topProductsQuery->whereDate('orders.created_at', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $topProductsQuery->whereDate('orders.created_at', '<=', $toDate);
        }
        $topProducts = $topProductsQuery
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $reviewPendingCount = ProductReview::where('approved', false)->count();
        $couponActiveCount = Coupon::where('is_active', true)->count();
        $couponUsedTotal = (int) Coupon::sum('used_count');
        $couponPerformance = Coupon::query()
            ->select('code', 'type', 'value', 'used_count', 'usage_limit', 'is_active')
            ->orderByDesc('used_count')
            ->limit(10)
            ->get();

        $vendorCommissionQuery = Vendor::query()
            ->select(
                'vendors.id',
                'vendors.name',
                'vendors.commission_rate',
                DB::raw('COALESCE(SUM(order_items.line_total), 0) as gross_sales')
            )
            ->leftJoin('products', 'products.vendor_id', '=', 'vendors.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where(function ($query) {
                $query->whereNull('orders.status')->orWhere('orders.status', '!=', 'cancelled');
            });

        if (!empty($fromDate)) {
            $vendorCommissionQuery->where(function ($query) use ($fromDate) {
                $query->whereNull('orders.created_at')->orWhereDate('orders.created_at', '>=', $fromDate);
            });
        }
        if (!empty($toDate)) {
            $vendorCommissionQuery->where(function ($query) use ($toDate) {
                $query->whereNull('orders.created_at')->orWhereDate('orders.created_at', '<=', $toDate);
            });
        }

        $vendorCommissions = $vendorCommissionQuery
            ->groupBy('vendors.id', 'vendors.name', 'vendors.commission_rate')
            ->orderByDesc('gross_sales')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $gross = (float) $row->gross_sales;
                $rate = (float) $row->commission_rate;
                $commission = round($gross * ($rate / 100), 2);
                return (object) [
                    'id' => $row->id,
                    'name' => $row->name,
                    'commission_rate' => $rate,
                    'gross_sales' => $gross,
                    'commission_amount' => $commission,
                    'net_vendor_amount' => max($gross - $commission, 0),
                ];
            });

        $payoutTotals = [
            'pending' => ['count' => 0, 'net_amount' => 0.0],
            'paid' => ['count' => 0, 'net_amount' => 0.0],
        ];

        if (Schema::hasTable('vendor_payouts')) {
            $pendingQuery = VendorPayout::query()->where('status', 'pending');
            $paidQuery = VendorPayout::query()->where('status', 'paid');

            if (!empty($fromDate)) {
                $pendingQuery->whereDate('period_start', '>=', $fromDate);
                $paidQuery->whereDate('period_start', '>=', $fromDate);
            }
            if (!empty($toDate)) {
                $pendingQuery->whereDate('period_end', '<=', $toDate);
                $paidQuery->whereDate('period_end', '<=', $toDate);
            }

            $payoutTotals['pending']['count'] = (int) $pendingQuery->count();
            $payoutTotals['pending']['net_amount'] = (float) $pendingQuery->sum('net_amount');

            $payoutTotals['paid']['count'] = (int) $paidQuery->count();
            $payoutTotals['paid']['net_amount'] = (float) $paidQuery->sum('net_amount');
        }

        return view('admin.reports.index', compact(
            'fromDate',
            'toDate',
            'totalOrders',
            'totalRevenue',
            'averageOrderValue',
            'statusBreakdown',
            'topProducts',
            'reviewPendingCount',
            'couponActiveCount',
            'couponUsedTotal',
            'couponPerformance',
            'vendorCommissions',
            'payoutTotals'
        ));
    }

    public function exportVendorCommissionsCsv(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = Vendor::query()
            ->select(
                'vendors.name',
                'vendors.commission_rate',
                DB::raw('COALESCE(SUM(order_items.line_total), 0) as gross_sales')
            )
            ->leftJoin('products', 'products.vendor_id', '=', 'vendors.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where(function ($q) {
                $q->whereNull('orders.status')->orWhere('orders.status', '!=', 'cancelled');
            });

        if (!empty($fromDate)) {
            $query->where(function ($q) use ($fromDate) {
                $q->whereNull('orders.created_at')->orWhereDate('orders.created_at', '>=', $fromDate);
            });
        }
        if (!empty($toDate)) {
            $query->where(function ($q) use ($toDate) {
                $q->whereNull('orders.created_at')->orWhereDate('orders.created_at', '<=', $toDate);
            });
        }

        $rows = $query
            ->groupBy('vendors.name', 'vendors.commission_rate')
            ->orderByDesc('gross_sales')
            ->get();

        $filename = 'vendor-commissions-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Vendor', 'Commission Rate (%)', 'Gross Sales', 'Commission Amount', 'Net Vendor Amount']);
            foreach ($rows as $row) {
                $gross = (float) $row->gross_sales;
                $rate = (float) $row->commission_rate;
                $commission = round($gross * ($rate / 100), 2);
                $net = max($gross - $commission, 0);
                fputcsv($output, [
                    $row->name,
                    number_format($rate, 2, '.', ''),
                    number_format($gross, 2, '.', ''),
                    number_format($commission, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
