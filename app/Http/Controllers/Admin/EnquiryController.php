<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query()->latest();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%");
            });
        }

        $enquiries = $query->paginate(15)->withQueryString();

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'enquiryCount' => ContactMessage::count(),
            'todayCount' => ContactMessage::whereDate('created_at', today())->count(),
        ]);
    }

    public function show(ContactMessage $enquiry)
    {
        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function destroy(ContactMessage $enquiry)
    {
        $enquiry->delete();

        return redirect()
            ->route('admin.enquiries.index')
            ->with('success', 'Enquiry deleted successfully.');
    }
}