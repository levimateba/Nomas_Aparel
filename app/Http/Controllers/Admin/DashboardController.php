<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogPost;
use App\Models\Contact;
use App\Models\ContactMessage;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier()) {
            return redirect()->route('admin.cashier.home');
        }
        if ($user && method_exists($user, 'isStockKeeper') && $user->isStockKeeper() && $user->hasPermission('view_inventory')) {
            return redirect()->route('admin.stock-overview.index');
        }

        $settings = Setting::get_settings();
        $now = now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $monthlyRevenue = (float) Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$thisMonthStart, $now])
            ->sum('total_amount');
        $lastMonthRevenue = (float) Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_amount');

        $productsThisMonth = Product::where('created_at', '>=', $thisMonthStart)->count();
        $productsLastMonth = Product::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $pendingThisMonth = Order::whereIn('status', ['pending', 'processing'])->where('created_at', '>=', $thisMonthStart)->count();
        $pendingLastMonth = Order::whereIn('status', ['pending', 'processing'])->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $enquiriesThisMonth = ContactMessage::where('created_at', '>=', $thisMonthStart)->count();
        $enquiriesLastMonth = ContactMessage::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $subscribersThisMonth = NewsletterSubscriber::where('created_at', '>=', $thisMonthStart)->count();
        $subscribersLastMonth = NewsletterSubscriber::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $postsThisMonth = BlogPost::where('created_at', '>=', $thisMonthStart)->count();
        $postsLastMonth = BlogPost::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $salesDays = 14;
        $salesSeries = $this->dailySums(Order::query()->where('status', '!=', 'cancelled'), $salesDays);
        $orderSeries = $this->dailyCounts(Order::query()->where('status', '!=', 'cancelled'), $salesDays);
        $paidOrderCount = Order::query()->where('status', '!=', 'cancelled')->count();
        $customerCount = (int) Order::query()
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->selectRaw('COUNT(DISTINCT customer_email) as aggregate')
            ->value('aggregate');
        if ($customerCount === 0) {
            $customerCount = (int) Order::query()
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', '')
                ->selectRaw('COUNT(DISTINCT customer_name) as aggregate')
                ->value('aggregate');
        }

        $shopCustomerCount = \Illuminate\Support\Facades\Schema::hasTable('shop_customers')
            ? (int) \App\Models\ShopCustomer::query()->count()
            : $customerCount;

        $todaySales = (float) Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', $now->toDateString())
            ->sum('total_amount');
        $yesterdaySales = (float) Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', $now->copy()->subDay()->toDateString())
            ->sum('total_amount');

        $inactiveProductCount = Product::where('is_active', false)->count();
        $avgOrderValue = $paidOrderCount > 0
            ? (float) Order::query()->where('status', '!=', 'cancelled')->avg('total_amount')
            : 0.0;

        // Prefer whereHas over HAVING on withCount — SQLite rejects HAVING on non-aggregate queries.
        $categoryBreakdown = Category::query()
            ->whereHas('products', fn ($q) => $q->where('is_active', true))
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('products_count')
            ->limit(6)
            ->get()
            ->map(fn ($cat) => [
                'name' => $cat->name,
                'count' => (int) $cat->products_count,
            ])
            ->values()
            ->all();

        return view('admin.dashboard', [
            'settings' => $settings,
            'contactCount' => Contact::count(),
            'enquiryCount' => ContactMessage::count(),
            'subscriberCount' => NewsletterSubscriber::count(),
            'blogCount' => BlogPost::count(),
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'activeProductCount' => Product::where('is_active', true)->count(),
            'inactiveProductCount' => $inactiveProductCount,
            'lowStockCount' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
            'outOfStockCount' => Product::where('is_active', true)->where('stock', '<=', 0)->count(),
            'orderCount' => Order::count(),
            'pendingOrderCount' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'monthlyRevenue' => $monthlyRevenue,
            'todaySales' => $todaySales,
            'shopCustomerCount' => $shopCustomerCount,
            'recentOrders' => Order::query()->with('user')->latest()->limit(6)->get(),
            'lowStockProducts' => Product::query()
                ->with('category')
                ->where('is_active', true)
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->limit(6)
                ->get(),
            'trends' => [
                'products' => $this->trendPercent($productsThisMonth, $productsLastMonth),
                'pending' => $this->trendPercent($pendingThisMonth, $pendingLastMonth),
                'revenue' => $this->trendPercent($monthlyRevenue, $lastMonthRevenue),
                'sales' => $this->trendPercent($todaySales, $yesterdaySales),
                'enquiries' => $this->trendPercent($enquiriesThisMonth, $enquiriesLastMonth),
                'subscribers' => $this->trendPercent($subscribersThisMonth, $subscribersLastMonth),
                'blog' => $this->trendPercent($postsThisMonth, $postsLastMonth),
                'lowStock' => null,
            ],
            'orderSparkline' => $this->dailyCounts(Order::query(), 7),
            'productSparkline' => $this->dailyCounts(Product::query(), 7),
            'revenueSparkline' => $this->dailySums(Order::query()->where('status', '!=', 'cancelled'), 7),
            'salesSparkline' => $this->dailySums(Order::query()->where('status', '!=', 'cancelled'), 7),
            'customerSparkline' => $this->dailyCounts(
                \Illuminate\Support\Facades\Schema::hasTable('shop_customers')
                    ? \App\Models\ShopCustomer::query()
                    : Order::query(),
                7
            ),
            'pendingSparkline' => $this->dailyCounts(Order::query()->whereIn('status', ['pending', 'processing']), 7),
            'lowStockSparkline' => $this->dailyCounts(Product::query()->where('is_active', true)->where('stock', '<=', 5), 7),
            'salesChart' => [
                'labels' => collect(range($salesDays - 1, 0))->map(fn ($i) => now()->subDays($i)->format('d M'))->values()->all(),
                'revenue' => $salesSeries,
                'orders' => $orderSeries,
            ],
            'salesSummary' => [
                'total_sales' => array_sum($salesSeries),
                'total_orders' => array_sum($orderSeries),
                'customers' => $customerCount,
                'avg_order' => $avgOrderValue,
            ],
            'categoryBreakdown' => $categoryBreakdown,
            'inventoryByLocation' => \Illuminate\Support\Facades\Schema::hasTable('inventory_stocks')
                ? app(\App\Services\InventoryStockService::class)->locationTotals()
                : [],
            'stockLocations' => \Illuminate\Support\Facades\Schema::hasTable('stock_locations')
                ? \App\Models\StockLocation::orderedActive()
                : collect(),
        ]);
    }

    public function settings()
    {
        $settings = Setting::get_settings();
        $onlineSalesLocations = \Illuminate\Support\Facades\Schema::hasTable('online_sales_locations')
            ? \App\Models\OnlineSalesLocation::query()->with('location')->orderBy('priority')->get()
            : collect();

        return view('admin.settings.edit', [
            'settings' => $settings,
            'fontSizes' => \App\Support\SystemTypography::SIZES,
            'currentFontSize' => $settings->system_font_size ?: \App\Support\SystemTypography::defaultSize(),
            'receiptPrintModes' => \App\Support\ReceiptPrintMode::options(),
            'stockLocations' => \Illuminate\Support\Facades\Schema::hasTable('stock_locations')
                ? \App\Models\StockLocation::orderedActive()
                : collect(),
            'onlineSalesLocations' => $onlineSalesLocations,
            'selectedOnlineLocationIds' => $onlineSalesLocations->pluck('stock_location_id')->map(fn ($id) => (int) $id)->all(),
            'onlineLocationPriorities' => $onlineSalesLocations->pluck('priority', 'stock_location_id')->all(),
        ]);
    }

    public function update(Request $request)
    {
        $settings = Setting::get_settings();

        $data = $request->validate([
            'site_name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'business_registration_number' => 'nullable|string|max:255',
            'tax_pin' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:80',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'currency' => 'required|string|max:8',
            'address' => 'nullable|string|max:2000',
            'receipt_header' => 'nullable|string|max:2000',
            'receipt_footer' => 'nullable|string|max:2000',
            'receipt_print_mode' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Support\ReceiptPrintMode::options()))],
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'system_font_size' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Support\SystemTypography::SIZES))],
            'site_tagline' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
            'remove_logo' => 'nullable|boolean',
            'pos_location_id' => 'nullable|exists:stock_locations,id',
            'online_sales_location_id' => 'nullable|exists:stock_locations,id',
            'online_sales_stock_mode' => 'nullable|in:single,selected,all',
            'online_fulfilment_strategy' => 'nullable|in:priority,manual',
            'online_sales_location_ids' => 'nullable|array',
            'online_sales_location_ids.*' => 'integer|exists:stock_locations,id',
            'online_location_priority' => 'nullable|array',
            'online_location_priority.*' => 'nullable|integer|min:1',
        ]);

        if ($request->boolean('remove_logo') && $settings->logoStoragePath()) {
            try {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($settings->logoStoragePath());
            } catch (\Throwable) {
            }
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        } else {
            unset($data['logo']);
        }

        $data['receipt_print_mode'] = \App\Support\ReceiptPrintMode::normalize($data['receipt_print_mode'] ?? null);
        $data['trading_name'] = $data['trading_name'] ?: $data['site_name'];
        $data['auto_open_drawer_cash'] = $request->boolean('auto_open_drawer_cash');
        $data['auto_print_receipt'] = $request->boolean('auto_print_receipt');
        $data['escpos_enabled'] = $request->boolean('escpos_enabled');
        $data['logo_show_on_login'] = $request->boolean('logo_show_on_login');
        $data['logo_show_on_sidebar'] = $request->boolean('logo_show_on_sidebar');
        $data['logo_show_on_receipts'] = $request->boolean('logo_show_on_receipts');
        $data['tax_enabled'] = $request->boolean('tax_enabled');
        $data['tax_inclusive'] = $request->boolean('tax_inclusive');
        $data['require_open_shift'] = $request->boolean('require_open_shift');
        $data['enforce_credit_limit'] = $request->boolean('enforce_credit_limit');
        $data['allow_negative_stock'] = $request->boolean('allow_negative_stock');
        if (\Illuminate\Support\Facades\Schema::hasColumn('settings', 'pos_sell_from_all_locations')) {
            $data['pos_sell_from_all_locations'] = $request->boolean('pos_sell_from_all_locations');
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('settings', 'pos_location_id')) {
            $mode = $request->input('online_sales_stock_mode', 'single');
            $data['pos_location_id'] = $request->input('pos_location_id') ?: null;
            $data['online_sales_stock_mode'] = $mode;
            $data['online_fulfilment_strategy'] = $request->input('online_fulfilment_strategy', 'priority');

            if ($mode === 'single') {
                $singleId = (int) $request->input('online_sales_location_id');
                if ($singleId <= 0) {
                    return back()->withErrors(['online_sales_location_id' => 'Select an online sales inventory location.'])->withInput();
                }
                $active = \App\Models\StockLocation::query()->whereKey($singleId)->where('is_active', true)->exists();
                if (! $active) {
                    return back()->withErrors(['online_sales_location_id' => 'Online sales location must be an active inventory location.'])->withInput();
                }
                $data['online_sales_location_id'] = $singleId;
                app(\App\Services\InventoryStockService::class)->syncOnlineSalesLocations([$singleId], [$singleId => 1]);
            } elseif ($mode === 'selected') {
                $ids = collect($request->input('online_sales_location_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();
                if ($ids->isEmpty()) {
                    return back()->withErrors(['online_sales_location_ids' => 'Select at least one online sales inventory location.'])->withInput();
                }
                $validCount = \App\Models\StockLocation::query()->whereIn('id', $ids)->where('is_active', true)->count();
                if ($validCount !== $ids->count()) {
                    return back()->withErrors(['online_sales_location_ids' => 'All selected locations must be active.'])->withInput();
                }
                $priorities = [];
                foreach ($ids as $index => $id) {
                    $priorities[$id] = (int) ($request->input('online_location_priority.'.$id) ?: ($index + 1));
                }
                asort($priorities);
                $orderedIds = array_keys($priorities);
                $normalized = [];
                foreach (array_values($orderedIds) as $i => $id) {
                    $normalized[$id] = $i + 1;
                }
                $data['online_sales_location_id'] = $orderedIds[0] ?? null;
                app(\App\Services\InventoryStockService::class)->syncOnlineSalesLocations($orderedIds, $normalized);
            } else {
                // all locations — keep primary location as Main Store for reference
                $data['online_sales_location_id'] = $request->input('online_sales_location_id')
                    ?: \App\Models\StockLocation::mainStore()?->id
                    ?: $settings->online_sales_location_id;
                app(\App\Services\InventoryStockService::class)->syncOnlineSalesLocations([]);
            }

            if ($data['pos_location_id']) {
                $posOk = \App\Models\StockLocation::query()->whereKey($data['pos_location_id'])->where('is_active', true)->exists();
                if (! $posOk) {
                    return back()->withErrors(['pos_location_id' => 'POS location must be an active inventory location.'])->withInput();
                }
            }
        }

        unset($data['online_sales_location_ids'], $data['online_location_priority']);
        if (! \Illuminate\Support\Facades\Schema::hasColumn('settings', 'online_fulfilment_strategy')) {
            unset($data['online_fulfilment_strategy']);
        }

        $settings->update($data);

        Audit::log('settings_updated', 'Updated store / receipt settings', $settings, [], 'settings');

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Business & receipt settings saved.');
    }

    private function trendPercent(int|float $current, int|float $previous): ?int
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : null;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    private function dailyCounts($query, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : 'DATE(created_at)';

        $rows = (clone $query)
            ->where('created_at', '>=', $start)
            ->selectRaw($dateExpr . ' as day, COUNT(*) as total')
            ->groupByRaw($dateExpr)
            ->pluck('total', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $series[] = (int) ($rows[$day] ?? 0);
        }

        return $series;
    }

    private function dailySums($query, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : 'DATE(created_at)';

        $rows = (clone $query)
            ->where('created_at', '>=', $start)
            ->selectRaw($dateExpr . ' as day, SUM(total_amount) as total')
            ->groupByRaw($dateExpr)
            ->pluck('total', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $series[] = round((float) ($rows[$day] ?? 0), 2);
        }

        return $series;
    }
}
