<?php

namespace App\Http\Controllers\Admin;

use App\Models\AboutSection;
use App\Models\BlogPost;
use App\Models\Contact;
use App\Models\ContactMessage;
use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\NewsEvent;
use App\Models\NewsletterSubscriber;
use App\Models\Price;
use App\Models\Product;
use App\Models\Order;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Video;
use App\Models\Quote;
use App\Models\Quotation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $settings = Setting::get_settings();
        $plans = Price::latest()->get();
        $orders = Order::query();

        return view('admin.dashboard', [
            'settings' => $settings,
            'aboutCount' => AboutSection::count(),
            'serviceCount' => Service::count(),
            'contactCount' => Contact::count(),
            'enquiryCount' => ContactMessage::count(),
            'subscriberCount' => NewsletterSubscriber::count(),
            'quoteCount' => Quote::count(),
            'newQuoteCount' => Quote::where('status', 'new')->count(),
            'quotationCount' => Quotation::count(),
            'blogCount' => BlogPost::count(),
            'galleryCount' => GalleryItem::count(),
            'newsEventCount' => NewsEvent::count(),
            'videoCount' => Video::count(),
            'teamCount' => TeamMember::count(),
            'priceCount' => $plans->count(),
            'featuredPlanCount' => $plans->where('featured', true)->count(),
            'pricingFeatureCount' => $plans->sum(fn ($plan) => is_array($plan->features) ? count($plan->features) : 0),
            'latestPlans' => $plans->take(3),
            'productCount' => Product::count(),
            'activeProductCount' => Product::where('is_active', true)->count(),
            'lowStockCount' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
            'orderCount' => $orders->count(),
            'pendingOrderCount' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'monthlyRevenue' => (float) Order::where('status', '!=', 'cancelled')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount'),
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
            ->route('admin.dashboard')
            ->with('success', 'Site settings updated successfully.');
    }
}
