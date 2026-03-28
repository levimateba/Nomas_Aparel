<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

use App\Models\AboutSection;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\Contact;
use App\Models\BlogPost;
use App\Models\NewsEvent;
use App\Models\Price;
use App\Models\TeamMember;
use App\Models\Video;

class HomeController extends Controller
{
    public function index()
    {
        $about = AboutSection::latest()->get();
        $services = Service::latest()->get();
        $contacts = Contact::latest()->get();
        $pricePackages = Price::latest()->get();
        $posts = BlogPost::where('published', true)->latest()->limit(3)->get();
        $galleryItems = GalleryItem::latest()->limit(6)->get();
        $newsEvents = NewsEvent::where('published', true)->latest('event_date')->limit(3)->get();
        $videos = Video::latest()->limit(3)->get();
        $teamMembers = TeamMember::latest()->limit(6)->get();

        return view('frontend.index', compact('about', 'services', 'contacts', 'pricePackages', 'posts', 'galleryItems', 'newsEvents', 'videos', 'teamMembers'));
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Save contact to database
            Contact::create($data);

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

            return back()->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again later.');
        }
    }
}
