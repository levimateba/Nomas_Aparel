<?php

namespace App\Http\Controllers\Admin;

use App\Models\AboutSection;
use App\Models\BlogPost;
use App\Models\Contact;
use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\NewsEvent;
use App\Models\Price;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Video;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $settings = Setting::get_settings();
        $plans = Price::latest()->get();

        return view('admin.dashboard', [
            'settings' => $settings,
            'aboutCount' => AboutSection::count(),
            'serviceCount' => Service::count(),
            'contactCount' => Contact::count(),
            'blogCount' => BlogPost::count(),
            'galleryCount' => GalleryItem::count(),
            'newsEventCount' => NewsEvent::count(),
            'videoCount' => Video::count(),
            'teamCount' => TeamMember::count(),
            'priceCount' => $plans->count(),
            'featuredPlanCount' => $plans->where('featured', true)->count(),
            'pricingFeatureCount' => $plans->sum(fn ($plan) => is_array($plan->features) ? count($plan->features) : 0),
            'latestPlans' => $plans->take(3),
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
