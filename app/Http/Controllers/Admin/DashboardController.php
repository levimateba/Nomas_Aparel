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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('admin.dashboard', [
            'settings' => $settings,
            'contactCount' => Contact::count(),
            'enquiryCount' => ContactMessage::count(),
            'subscriberCount' => NewsletterSubscriber::count(),
            'blogCount' => BlogPost::count(),
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'activeProductCount' => Product::where('is_active', true)->count(),
            'lowStockCount' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
            'orderCount' => Order::count(),
            'pendingOrderCount' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'monthlyRevenue' => $monthlyRevenue,
            'recentOrders' => Order::query()->latest()->limit(6)->get(),
            'lowStockProducts' => Product::query()
                ->where('is_active', true)
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->limit(6)
                ->get(),
            'trends' => [
                'products' => $this->trendPercent($productsThisMonth, $productsLastMonth),
                'pending' => $this->trendPercent($pendingThisMonth, $pendingLastMonth),
                'revenue' => $this->trendPercent($monthlyRevenue, $lastMonthRevenue),
                'enquiries' => $this->trendPercent($enquiriesThisMonth, $enquiriesLastMonth),
                'subscribers' => $this->trendPercent($subscribersThisMonth, $subscribersLastMonth),
                'blog' => $this->trendPercent($postsThisMonth, $postsLastMonth),
            ],
            'orderSparkline' => $this->dailyCounts(Order::query(), 7),
            'productSparkline' => $this->dailyCounts(Product::query(), 7),
        ]);
    }

    public function settings()
    {
        return view('admin.settings.edit', [
            'settings' => Setting::get_settings(),
        ]);
    }

    public function update(Request $request)
    {
        $settings = Setting::get_settings();

        $data = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        $settings->update($data);

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Site settings updated successfully.');
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
}
