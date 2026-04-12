<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Quotation;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::withCount('items')->latest()->paginate(15);

        return view('admin.quotations.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        $fromQuote = null;
        $prefill = [
            'status'       => 'draft',
            'tax'          => 0,
            'subtotal'     => 0,
            'total_amount' => 0,
        ];

        if ($request->filled('from_quote')) {
            $fromQuote = Quote::findOrFail($request->from_quote);
            $prefill = array_merge($prefill, [
                'client_name'   => $fromQuote->name,
                'client_email'  => $fromQuote->email,
                'client_phone'  => $fromQuote->phone,
                'project_title' => $fromQuote->service_type,
                'description'   => $fromQuote->project_details,
                'timeline'      => $fromQuote->timeline
                    ? \Carbon\Carbon::parse($fromQuote->timeline)->toDateString()
                    : null,
            ]);
        }

        return view('admin.quotations.create', [
            'quotation' => new Quotation($prefill),
            'fromQuote' => $fromQuote,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        DB::transaction(function () use ($data, $request) {
            $quotation = Quotation::create([
                'client_name'    => $data['client_name'],
                'client_email'   => $data['client_email'],
                'client_phone'   => $data['client_phone'] ?? null,
                'client_address' => $data['client_address'] ?? null,
                'project_title'  => $data['project_title'],
                'description'    => $data['description'] ?? null,
                'scope_of_work'  => $data['scope_of_work'] ?? null,
                'deliverables'   => $data['deliverables'] ?? null,
                'timeline'       => $data['timeline'] ?? null,
                'subtotal'       => $data['subtotal'],
                'tax'            => $data['tax'] ?? 0,
                'total_amount'   => $data['total_amount'],
                'status'         => $data['status'],
                'created_by'     => auth()->id(),
                'quote_id'       => $request->input('quote_id') ?: null,
            ]);

            $quotation->items()->createMany($this->buildItems($data['items']));

            // Auto-mark the linked quote request as 'quoted' if it was 'new' or 'reviewing'
            if ($quotation->quote_id) {
                Quote::where('id', $quotation->quote_id)
                    ->whereIn('status', ['new', 'reviewing'])
                    ->update(['status' => 'quoted']);
            }
        });

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items');

        return view('admin.quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $this->validateRequest($request);

        DB::transaction(function () use ($quotation, $data) {
            $quotation->update([
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'client_phone' => $data['client_phone'] ?? null,
                'client_address' => $data['client_address'] ?? null,
                'project_title' => $data['project_title'],
                'description' => $data['description'] ?? null,
                'scope_of_work' => $data['scope_of_work'] ?? null,
                'deliverables' => $data['deliverables'] ?? null,
                'timeline' => $data['timeline'] ?? null,
                'subtotal' => $data['subtotal'],
                'tax' => $data['tax'] ?? 0,
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
            ]);

            $quotation->items()->delete();
            $quotation->items()->createMany($this->buildItems($data['items']));
        });

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted successfully.');
    }

    public function generatePDF(Quotation $quotation)
    {
        $quotation->load('items');
        $settings = Setting::get_settings();

        $pdf = Pdf::loadView('admin.quotations.pdf', [
            'quotation' => $quotation,
            'settings' => $settings,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }

    public function sendEmail(Quotation $quotation)
    {
        $quotation->load('items');
        $settings = Setting::get_settings();

        $pdf = Pdf::loadView('admin.quotations.pdf', [
            'quotation' => $quotation,
            'settings' => $settings,
            'generatedAt' => now(),
        ])->output();

        Mail::send([], [], function ($message) use ($quotation, $pdf, $settings) {
            $message->to($quotation->client_email, $quotation->client_name)
                ->subject('Quotation ' . $quotation->quotation_number . ' - ' . ($settings->site_name ?? 'ICT Consultancy'))
                ->setBody('Dear ' . $quotation->client_name . ',<br><br>Please find attached your quotation <strong>' . $quotation->quotation_number . '</strong>.<br><br>Kind regards,<br>' . ($settings->site_name ?? 'ICT Consultancy'), 'text/html')
                ->attachData($pdf, 'quotation-' . $quotation->quotation_number . '.pdf', [
                    'mime' => 'application/pdf',
                ]);
        });

        $quotation->update(['status' => 'sent']);

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation emailed with PDF attachment.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_address' => 'nullable|string|max:500',
            'project_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'timeline' => 'nullable|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,approved,sent',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);
    }

    private function buildItems(array $items): array
    {
        return collect($items)->map(function (array $item) {
            return [
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'total_price' => (float) $item['total_price'],
            ];
        })->values()->all();
    }
}
