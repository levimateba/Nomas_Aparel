<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('company', 'like', "%{$term}%")
                  ->orWhere('service_type', 'like', "%{$term}%");
            });
        }

        $quotes  = $query->paginate(15)->withQueryString();
        $counts  = [
            'all'       => Quote::count(),
            'new'       => Quote::where('status', 'new')->count(),
            'reviewing' => Quote::where('status', 'reviewing')->count(),
            'quoted'    => Quote::where('status', 'quoted')->count(),
            'accepted'  => Quote::where('status', 'accepted')->count(),
            'declined'  => Quote::where('status', 'declined')->count(),
        ];

        return view('admin.quotes.index', compact('quotes', 'counts'));
    }

    public function show(Quote $quote)
    {
        $quote->load('quotation');

        return view('admin.quotes.show', compact('quote'));
    }

    public function downloadPdf(Quote $quote)
    {
        $settings = Setting::get_settings();

        $pdf = Pdf::loadView('admin.quotes.pdf', [
            'quote' => $quote,
            'settings' => $settings,
            'generatedAt' => now(),
        ])->setPaper('a4');

        $fileName = 'quotation-' . $quote->id . '-' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($fileName);
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'status'      => 'required|in:new,reviewing,quoted,accepted,declined',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $quote->update($data);

        return redirect()->route('admin.quotes.show', $quote)->with('success', 'Quote updated successfully.');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('admin.quotes.index')->with('success', 'Quote request deleted.');
    }
}
