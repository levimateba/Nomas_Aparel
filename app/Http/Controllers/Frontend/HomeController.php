<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactMail;

use App\Models\Contact;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::query()
            ->with('category')
            ->when(Schema::hasTable('product_reviews'), function ($query) {
                $query->withCount(['reviews' => fn ($reviews) => $reviews->where('approved', true)])
                    ->withAvg(['reviews' => fn ($reviews) => $reviews->where('approved', true)], 'rating');
            })
            ->where('is_active', true)
            ->latest()
            ->limit(24)
            ->get();
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
        $deals = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->latest()
            ->limit(12)
            ->get();
        $heroImages = Product::query()
            ->where('is_active', true)
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->latest()
            ->limit(5)
            ->pluck('image_url');
        $contacts = Contact::latest()->get();

        return view('frontend.index', compact('featuredProducts', 'categories', 'deals', 'heroImages', 'contacts'));
    }

    public function contactPage()
    {
        return view('frontend.contact');
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Store enquiry safely in its own table.
        ContactMessage::create($data);

        try {
            // Send email to admin
            $admin_email = env('MAIL_FROM_ADDRESS', 'noreply@elgontech.com');
            Mail::to($admin_email)->send(new ContactMail(
                $data['name'],
                $data['email'],
                $data['subject'],
                $data['message']
            ));

            // Send confirmation email to user
            Mail::to($data['email'])->send(new \App\Mail\ContactConfirmationMail(
                $data['name'],
                $data['subject']
            ));
        } catch (\Exception $e) {
            return back()->with('success', 'Your enquiry was received successfully. We will contact you soon.')->with('error', 'We could not send confirmation email right now, but your enquiry is saved.');
        }

        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    public function subscribeNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'newsletter_email' => 'required|email|max:255|unique:newsletter_subscribers,email',
        ], [
            'newsletter_email.unique' => 'That email is already subscribed.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'newsletter')
                ->withInput();
        }

        NewsletterSubscriber::create([
            'email' => $request->newsletter_email,
        ]);

        return back()->with('newsletter_success', 'Subscription received successfully.');
    }
}
