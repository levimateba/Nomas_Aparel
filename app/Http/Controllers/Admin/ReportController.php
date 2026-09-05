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

    public function exportCashier(Request $request, StoreReportService $reports)
    {
        [$from, $to, $preset] = ReportPeriod::fromRequest($request, 'today');
        $cashierId = $request->query('cashier_id');
        $rows = $reports->byCashier($from, $to);

        if ($cashierId) {
            $rows = $rows->where('user_id', (int) $cashierId)->values();
        }

        $settings = Setting::get_settings();
        $pdf = Pdf::loadView('admin.reports.cashier-pdf', [
            'from' => $from,
            'to' => $to,
            'preset' => $preset,
            'rows' => $rows,
            'cashierId' => $cashierId,
            'cashierName' => $cashierId
                ? ($reports->cashiers()->firstWhere('id', (int) $cashierId)?->name ?? 'Cashier')
                : 'All cashiers',
            'settings' => $settings,
            'logoSrc' => $settings->logoDataUri(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('cashier-performance-'.now()->format('Ymd_His').'.pdf');
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
        $daily = $reports->daily($from, $to);
        $paymentBreakdown = $reports->paymentBreakdown($from, $to);

        $fromCarbon = \Illuminate\Support\Carbon::parse($from)->startOfDay();
        $toCarbon = \Illuminate\Support\Carbon::parse($to)->startOfDay();
        $days = max($fromCarbon->diffInDays($toCarbon) + 1, 1);
        $prevTo = $fromCarbon->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1);
        $prevSummary = $reports->summary($prevFrom->toDateString(), $prevTo->toDateString());

        $trend = function (float $current, float $previous): ?float {
            if ($previous <= 0) {
                return $current > 0 ? 100.0 : null;
            }

            return round((($current - $previous) / $previous) * 100, 0);
        };

        $chartLabels = $daily->pluck('label')->values()->all();
        $chartSales = $daily->pluck('sales')->map(fn ($v) => round((float) $v, 2))->values()->all();

        // For a single day, prefer hourly buckets when available.
        if ($from === $to) {
            $driver = DB::getDriverName();
            $hourExpr = match ($driver) {
                'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
                'pgsql' => 'EXTRACT(HOUR FROM created_at)::integer',
                default => 'HOUR(created_at)',
            };

            $hourly = Order::query()
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', $from)
                ->selectRaw("{$hourExpr} as hour")
                ->selectRaw('SUM(total_amount) as total')
                ->groupBy('hour')
                ->pluck('total', 'hour');

            $chartLabels = [];
            $chartSales = [];
            for ($h = 0; $h <= 23; $h++) {
                $chartLabels[] = sprintf('%02d:00', $h);
                $chartSales[] = round((float) ($hourly[$h] ?? 0), 2);
            }
        }

        $payLabels = [];
        $paySeries = [];
        $payColors = ['#a58112', '#12B76A', '#465FFF', '#F79009', '#7A5AF8', '#EE46BC'];
        foreach ($paymentBreakdown as $row) {
            $payLabels[] = ucwords(str_replace('_', ' ', $row->payment_method ?: 'Other'));
            $paySeries[] = round((float) $row->total, 2);
        }

        return [
            'from' => $from,
            'to' => $to,
            'preset' => $preset,
            'summary' => $summary,
            'prevSummary' => $prevSummary,
            'trends' => [
                'gross' => $trend((float) $summary['gross'], (float) $prevSummary['gross']),
                'net' => $trend((float) $summary['net'], (float) $prevSummary['net']),
                'count' => $trend((float) $summary['count'], (float) $prevSummary['count']),
                'discount' => $trend((float) $summary['discount'], (float) $prevSummary['discount']),
                'returns' => $trend((float) $summary['returns'], (float) $prevSummary['returns']),
                'average' => $trend((float) $summary['average'], (float) $prevSummary['average']),
                'pos' => $trend((float) $summary['pos_revenue'], (float) $prevSummary['pos_revenue']),
                'online' => $trend((float) $summary['online_revenue'], (float) $prevSummary['online_revenue']),
            ],
            'trendLabel' => match ($preset) {
                'today' => 'vs yesterday',
                'yesterday' => 'vs prior day',
                'week' => 'vs prior week',
                'month' => 'vs prior month',
                default => 'vs prior period',
            },
            'daily' => $daily,
            'paymentBreakdown' => $paymentBreakdown,
            'statusBreakdown' => $reports->statusBreakdown($from, $to),
            'topProducts' => $reports->topProducts($from, $to),
            'categorySales' => $reports->categorySales($from, $to),
            'lowStock' => $reports->lowStock(),
            'locations' => Schema::hasTable('stock_locations')
                ? \App\Models\StockLocation::orderedActive()
                : collect(),
            'chart' => [
                'labels' => $chartLabels,
                'sales' => $chartSales,
            ],
            'paymentChart' => [
                'labels' => $payLabels,
                'series' => $paySeries,
                'colors' => array_slice($payColors, 0, max(count($payLabels), 1)),
                'total' => array_sum($paySeries),
            ],
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

    public function onlineSalesByLocation(Request $request)
    {
        abort_unless(Schema::hasTable('order_stock_allocations'), 404);

        [$from, $to, $preset] = ReportPeriod::fromRequest($request, 'month');

        $fromAt = ReportPeriod::openingAt($from);
        $toAt = ReportPeriod::closingAt($to);

        $rows = \App\Models\OrderStockAllocation::query()
            ->selectRaw('stock_location_id, SUM(quantity) as units, COUNT(DISTINCT order_id) as orders_count')
            ->whereBetween('created_at', [$fromAt, $toAt])
            ->groupBy('stock_location_id')
            ->with('location')
            ->get();

        $lines = \App\Models\OrderStockAllocation::query()
            ->with(['product', 'variant', 'location', 'order'])
            ->whereBetween('created_at', [$fromAt, $toAt])
            ->latest()
            ->limit(100)
            ->get();

        return view('admin.reports.online-sales-by-location', [
            'from' => $from,
            'to' => $to,
            'preset' => $preset,
            'rows' => $rows,
            'lines' => $lines,
            'totalUnits' => (int) $rows->sum('units'),
        ]);
    }

    public function stockValuation(Request $request, \App\Services\StockValuationService $valuation)
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'category' => $request->input('category'),
            'brand' => $request->input('brand'),
            'stock' => $request->string('stock')->toString(),
        ];
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $products = $valuation->inventoryQuery($filters)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $categories = collect();
        if (Schema::hasTable('categories')) {
            $categories = \App\Models\Category::query()
                ->when(Schema::hasColumn('categories', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $brands = collect();
        if (Schema::hasTable('product_brands')) {
            $brands = \App\Models\ProductBrand::query()->orderBy('name')->get(['id', 'name']);
        } elseif (Schema::hasTable('brands')) {
            $brands = DB::table('brands')->orderBy('name')->get(['id', 'name']);
        }

        $pendingPos = collect();
        if (Schema::hasTable('purchase_orders')) {
            $pendingPos = \App\Models\PurchaseOrder::query()
                ->with('supplier')
                ->whereIn('status', ['draft', 'submitted', 'approved', 'partially_received'])
                ->latest('order_date')
                ->limit(10)
                ->get();
        }

        $stocktakes = collect();
        if (Schema::hasTable('stock_takes')) {
            $stocktakes = \App\Models\StockTake::query()
                ->where('status', 'approved')
                ->latest()
                ->limit(10)
                ->get();
        }

        $customerBalances = collect();
        if (Schema::hasTable('shop_customers') && Schema::hasColumn('shop_customers', 'balance')) {
            $customerBalances = \App\Models\ShopCustomer::query()
                ->where('balance', '>', 0)
                ->orderByDesc('balance')
                ->limit(10)
                ->get();
        }

        $supplierBalances = collect();
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'balance_due')) {
            $supplierBalances = \App\Models\Purchase::query()
                ->selectRaw('supplier_id, SUM(balance_due) as outstanding')
                ->where('balance_due', '>', 0)
                ->whereNotNull('supplier_id')
                ->groupBy('supplier_id')
                ->with('supplier')
                ->orderByDesc('outstanding')
                ->limit(10)
                ->get();
        }

        return view('admin.reports.stock-valuation', [
            'summary' => $valuation->summary($filters),
            'tableTotals' => $valuation->tableTotals($filters),
            'products' => $products,
            'valuation' => $valuation,
            'filters' => $filters,
            'perPage' => $perPage,
            'categories' => $categories,
            'brands' => $brands,
            'pendingPos' => $pendingPos,
            'stocktakes' => $stocktakes,
            'customerBalances' => $customerBalances,
            'supplierBalances' => $supplierBalances,
            'settings' => Setting::get_settings(),
        ]);
    }

    public function exportStockValuation(Request $request, \App\Services\StockValuationService $valuation)
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'category' => $request->input('category'),
            'brand' => $request->input('brand'),
            'stock' => $request->string('stock')->toString(),
        ];
        $format = strtolower((string) $request->query('format', 'pdf'));
        $products = $valuation->inventoryQuery($filters)->with(['brand', 'category'])->orderBy('name')->get();
        $settings = Setting::get_settings();
        $payload = [
            'summary' => $valuation->summary($filters),
            'tableTotals' => $valuation->tableTotals($filters),
            'products' => $products,
            'valuation' => $valuation,
            'settings' => $settings,
            'logoSrc' => $settings->logoDataUri(),
            'generatedAt' => now(),
        ];

        if ($format === 'excel' || $format === 'csv') {
            return response()->streamDownload(function () use ($products, $valuation) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['Product', 'SKU', 'Category', 'Qty', 'Buying', 'Selling', 'Stock Cost', 'Expected Sales', 'Expected Profit', 'Status']);
                foreach ($products as $product) {
                    $m = $valuation->lineMetrics($product);
                    fputcsv($out, [
                        $product->name,
                        $product->sku,
                        $product->category?->name,
                        $product->stock,
                        $product->buying_price,
                        $product->price,
                        $m['stock_cost'],
                        $m['expected_sales'],
                        $m['expected_profit'],
                        $valuation->stockStatus($product),
                    ]);
                }
                fclose($out);
            }, 'stock-valuation-'.now()->format('Ymd_His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $pdf = Pdf::loadView('admin.reports.stock-valuation-pdf', $payload)->setPaper('a4', 'landscape');

        return $pdf->download('stock-valuation-'.now()->format('Ymd_His').'.pdf');
    }
}
