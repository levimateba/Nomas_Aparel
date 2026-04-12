<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'company'         => 'nullable|string|max:255',
            'service_type'    => 'required|string|max:255',
            'budget_range'    => 'nullable|string|max:100',
            'timeline'        => 'nullable|date',
            'project_details' => 'required|string|max:5000',
        ]);

        Quote::create($data);

        return back()->with('quote_success', 'Thank you! Your quote request has been submitted. We will review it and get back to you shortly.');
    }
}
