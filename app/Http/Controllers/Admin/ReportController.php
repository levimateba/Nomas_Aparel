<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\Setting;
use App\Models\Vendor;
use App\Models\VendorPayout;
use App\Services\StoreReportService;
use App\Support\ReportPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request, StoreReportService $reports)
    {
        return view('admin.reports.index', $this->buildReport($request, $reports));
    }

    public function cashier(Request $request, StoreReportService $reports)
    {
        [$from, $to, $preset] = ReportPeriod::fromRequest($request, 'today');
        $cashierId = $request->query('cashier_id');
        $rows = $reports->byCashier($from, $to);

        if ($cashierId) {
            $rows = $rows->where('user_id', (int) $cashierId)->values();
        }

        return view('admin.reports.cashier', [
            'from' => $from,
            'to' => $to,
            'preset' => $preset,
            'rows' => $rows,
            'cashiers' => $reports->cashiers(),
            'cashierId' => $cashierId,
        ]);
    }

    public function export(Request $request, StoreReportService $reports)
    {
        $format = strtolower((string) $request->query('format', 'pdf'));
        $report = $this->buildReport($request, $reports);

        if ($format === 'excel' || $format === 'csv') {
            $filename = 'store-report-'.$report['from'].'_'.$report['to'].'.csv';

            return response()->streamDownload(function () use ($report) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Metric', 'Value']);
                foreach ([
                    'Sales' => $report['summary']['count'],
                    'Gross sales' => $report['summary']['gross'],
                    'Net sales' => $report['summary']['net'],
                    'Discounts' => $report['summary']['discount'],
                    'Returns' => $report['summary']['returns'],
                    'POS sales' => $report['summary']['pos_revenue'],
                    'Online sales' => $report['summary']['online_revenue'],
                ] as $label => $value) {
                    fputcsv($output, [$label, $value]);
                }
                fputcsv($output, []);
                fputcsv($output, ['Date', 'Sales', 'POS', 'Online', 'Returns', 'Tickets']);
                foreach ($report['daily'] as $row) {
                    fputcsv($output, [$row['date'], $row['sales'], $row['pos'], $row['online'], $row['returns'], $row['count']]);
                }
                fclose($output);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $pdf = Pdf::loadView('admin.reports.pdf', $report + [
            'settings' => Setting::get_settings(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('store-report-'.$report['from'].'_'.$report['to'].'.pdf');
    }

    public function exportVendorCommissionsCsv(Request $request)
    {
        [$from, $to] = ReportPeriod::fromRequest($request, 'month');
        $rows = $this->vendorCommissions($from, $to);
        $filename = 'vendor-commissions-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Vendor', 'Commission Rate (%)', 'Gross Sales', 'Commission Amount', 'Net Vendor Amount']);
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->name,
                    number_format((float) $row->commission_rate, 2, '.', ''),
                    number_format((float) $row->gross_sales, 2, '.', ''),
                    number_format((float) $row->commission_amount, 2, '.', ''),
                    number_format((float) $row->net_vendor_amount, 2, '.', ''),
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReport(Request $request, StoreReportService $reports): array
    {
        [$from, $to, $preset] = ReportPeriod::fromRequest($request, 'today');
        $summary = $reports->summary($from, $to);

        return [
            'from' => $from,
            'to' => $to,
            'preset' => $preset,
            'summary' => $summary,
            'daily' => $reports->daily($from, $to),
            'paymentBreakdown' => $reports->paymentBreakdown($from, $to),
            'statusBreakdown' => $reports->statusBreakdown($from, $to),
            'topProducts' => $reports->topProducts($from, $to),
            'categorySales' => $reports->categorySales($from, $to),
            'lowStock' => $reports->lowStock(),
            'recentOrders' => Order::query()
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->latest()
                ->limit(8)
                ->get(),
            'reviewPendingCount' => ProductReview::where('approved', false)->count(),
            'couponActiveCount' => Coupon::where('is_active', true)->count(),
            'couponUsedTotal' => (int) Coupon::sum('used_count'),
            'vendorCommissions' => $this->vendorCommissions($from, $to),
            'payoutTotals' => $this->payoutTotals($from, $to),
        ];
    }

    private function vendorCommissions(string $from, string $to)
    {
        $query = Vendor::query()
            ->select(
                'vendors.id',
                'vendors.name',
                'vendors.commission_rate',
                DB::raw('COALESCE(SUM(order_items.line_total), 0) as gross_sales')
            )
            ->leftJoin('products', 'products.vendor_id', '=', 'vendors.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where(function ($inner) {
                $inner->whereNull('orders.status')->orWhere('orders.status', '!=', 'cancelled');
            })
            ->where(function ($inner) use ($from) {
                $inner->whereNull('orders.created_at')->orWhereDate('orders.created_at', '>=', $from);
            })
            ->where(function ($inner) use ($to) {
                $inner->whereNull('orders.created_at')->orWhereDate('orders.created_at', '<=', $to);
            })
            ->groupBy('vendors.id', 'vendors.name', 'vendors.commission_rate')
            ->orderByDesc('gross_sales')
            ->limit(20)
            ->get();

        return $query->map(function ($row) {
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
    }

    private function payoutTotals(string $from, string $to): array
    {
        $totals = [
            'pending' => ['count' => 0, 'net_amount' => 0.0],
            'paid' => ['count' => 0, 'net_amount' => 0.0],
        ];

        if (! Schema::hasTable('vendor_payouts')) {
            return $totals;
        }

        foreach (['pending', 'paid'] as $status) {
            $query = VendorPayout::query()->where('status', $status)->whereDate('period_start', '>=', $from)->whereDate('period_end', '<=', $to);
            $totals[$status]['count'] = (int) $query->count();
            $totals[$status]['net_amount'] = (float) $query->sum('net_amount');
        }

        return $totals;
    }
}
