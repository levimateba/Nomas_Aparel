<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query()->latest();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('email', 'like', "%{$term}%");
        }

        $subscribers = $query->paginate(20)->withQueryString();

        return view('admin.newsletter-subscribers.index', [
            'subscribers' => $subscribers,
            'subscriberCount' => NewsletterSubscriber::count(),
            'todayCount' => NewsletterSubscriber::whereDate('created_at', today())->count(),
        ]);
    }

    public function destroy(NewsletterSubscriber $newsletter_subscriber)
    {
        $newsletter_subscriber->delete();

        return redirect()
            ->route('admin.newsletter-subscribers.index')
            ->with('success', 'Subscriber deleted successfully.');
    }
}