@extends('layouts.admin')
@section('title', 'Quote #' . $quote->id)
@section('content')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .detail-field label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #8fa0b0;
        margin-bottom: 4px;
        display: block;
    }
    .detail-field p {
        margin: 0;
        font-size: 0.97rem;
        color: #1a2a38;
        font-weight: 500;
    }
    .status-select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #dde3ea;
        border-radius: 10px;
        font-size: 0.95rem;
        background: #fff;
        box-sizing: border-box;
    }
    .notes-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #dde3ea;
        border-radius: 10px;
        font-size: 0.95rem;
        resize: vertical;
        min-height: 120px;
        box-sizing: border-box;
    }
    @media (max-width: 680px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="{{ route('admin.quotes.index') }}" style="color:#16456e;text-decoration:none;font-weight:600;font-size:0.9rem;">
        &larr; Back to Quotes
    </a>
    <span style="color:#ccc;">|</span>
    <h2 style="margin:0;">Quote Request #{{ $quote->id }}</h2>
    <span style="padding:5px 16px;border-radius:999px;color:#fff;font-size:0.78rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;background:{{ $quote->status_color }};">
        {{ $quote->status_label }}
    </span>
</div>

@if(session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr);gap:20px;align-items:start;">
    <!-- Left: Client & Project Details -->
    <div>
        <div class="card">
            <h3 style="margin:0 0 18px;color:var(--primary-dark);">Client Information</h3>
            <div class="detail-grid">
                <div class="detail-field">
                    <label>Full Name</label>
                    <p>{{ $quote->name }}</p>
                </div>
                <div class="detail-field">
                    <label>Email</label>
                    <p><a href="mailto:{{ $quote->email }}" style="color:var(--primary-green);">{{ $quote->email }}</a></p>
                </div>
                <div class="detail-field">
                    <label>Phone</label>
                    <p>{{ $quote->phone ?: '—' }}</p>
                </div>
                <div class="detail-field">
                    <label>Company / Organisation</label>
                    <p>{{ $quote->company ?: '—' }}</p>
                </div>
                <div class="detail-field">
                    <label>Service Requested</label>
                    <p>{{ $quote->service_type }}</p>
                </div>
                <div class="detail-field">
                    <label>Budget Range</label>
                    <p>{{ $quote->budget_range ?: '—' }}</p>
                </div>
                <div class="detail-field">
                    <label>Desired Timeline</label>
                    <p>{{ $quote->timeline ? $quote->timeline->format('F j, Y') : '—' }}</p>
                </div>
                <div class="detail-field">
                    <label>Submitted</label>
                    <p>{{ $quote->created_at->format('F j, Y \a\t H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin:0 0 14px;color:var(--primary-dark);">Project Details</h3>
            <p style="color:#444;line-height:1.8;margin:0;white-space:pre-line;">{{ $quote->project_details }}</p>
        </div>
    </div>

    <!-- Right: Status & Notes Update -->
    <div>
        <div class="card">
            <h3 style="margin:0 0 18px;color:var(--primary-dark);">Update Status &amp; Notes</h3>
            <form method="POST" action="{{ route('admin.quotes.update', $quote) }}">
                @csrf
                @method('PATCH')
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Status</label>
                    <select name="status" class="status-select">
                        @foreach(['new' => 'New','reviewing' => 'Reviewing','quoted' => 'Quoted','accepted' => 'Accepted','declined' => 'Declined'] as $val => $label)
                            <option value="{{ $val }}" {{ $quote->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Internal Notes</label>
                    <textarea name="admin_notes" class="notes-textarea" placeholder="Notes visible only to admins…">{{ old('admin_notes', $quote->admin_notes) }}</textarea>
                </div>
                <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#16456e,#165752);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:0.97rem;cursor:pointer;">
                    Save Changes
                </button>
            </form>
        </div>

        <div class="card" style="background:rgba(22,69,110,0.04);border:1.5px solid rgba(22,69,110,0.1);">
            <h4 style="margin:0 0 10px;color:var(--primary-dark);font-size:0.9rem;">Quick Actions</h4>
            <a href="{{ route('admin.quotes.pdf', $quote) }}"
                style="display:block;padding:10px 14px;background:#fff;border:1.5px solid #e0e7ef;border-radius:10px;color:#16456e;font-weight:600;font-size:0.88rem;text-decoration:none;margin-bottom:8px;">
                <i class="fa fa-file-pdf mr-2"></i>Generate Quotation PDF
            </a>
            <a href="mailto:{{ $quote->email }}?subject=Your Quote Request — {{ $quote->service_type }}"
                style="display:block;padding:10px 14px;background:#fff;border:1.5px solid #e0e7ef;border-radius:10px;color:#16456e;font-weight:600;font-size:0.88rem;text-decoration:none;margin-bottom:8px;">
                <i class="fa fa-envelope mr-2"></i>Reply via Email
            </a>
            <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" onsubmit="return confirm('Permanently delete this quote request?')">
                @csrf @method('DELETE')
                <button type="submit" style="width:100%;padding:10px 14px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border:none;border-radius:10px;font-weight:600;font-size:0.88rem;cursor:pointer;">
                    <i class="fa fa-trash mr-2"></i>Delete Request
                </button>
            </form>
        </div>

        {{-- Linked Quotation --}}
        @if($quote->quotation)
        <div class="card" style="border:2px solid #2e7d32;background:rgba(46,125,50,0.04);">
            <h4 style="margin:0 0 14px;color:#2e7d32;font-size:0.9rem;display:flex;align-items:center;gap:8px;">
                <i class="fa fa-file-invoice-dollar"></i> Linked Quotation
            </h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
                <div>
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#8fa0b0;margin-bottom:2px;">Number</div>
                    <div style="font-weight:700;color:#1a2a38;">{{ $quote->quotation->quotation_number }}</div>
                </div>
                <div>
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#8fa0b0;margin-bottom:2px;">Status</div>
                    <span style="padding:3px 10px;border-radius:999px;color:#fff;font-size:0.75rem;font-weight:700;background:{{ $quote->quotation->status_badge_color }};">
                        {{ ucfirst($quote->quotation->status) }}
                    </span>
                </div>
                <div>
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#8fa0b0;margin-bottom:2px;">Total</div>
                    <div style="font-weight:700;color:#1a2a38;">KES {{ number_format($quote->quotation->total_amount, 2) }}</div>
                </div>
                <div>
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#8fa0b0;margin-bottom:2px;">Items</div>
                    <div style="font-weight:700;color:#1a2a38;">{{ $quote->quotation->items()->count() }}</div>
                </div>
            </div>
            <a href="{{ route('admin.quotations.edit', $quote->quotation) }}"
                style="display:block;padding:10px 14px;background:linear-gradient(135deg,#2e7d32,#43a047);color:#fff;border-radius:10px;font-weight:700;font-size:0.88rem;text-decoration:none;text-align:center;margin-bottom:8px;">
                <i class="fa fa-edit mr-2"></i>Edit Quotation
            </a>
            <a href="{{ route('admin.quotations.pdf', $quote->quotation) }}"
                style="display:block;padding:10px 14px;background:#fff;border:1.5px solid #2e7d32;border-radius:10px;color:#2e7d32;font-weight:600;font-size:0.88rem;text-decoration:none;text-align:center;">
                <i class="fa fa-file-pdf mr-2"></i>Download PDF
            </a>
        </div>
        @else
        <div class="card" style="border:1.5px dashed #b0bec5;background:rgba(0,0,0,0.01);text-align:center;">
            <p style="color:#607d8b;font-size:0.88rem;margin:0 0 12px;">No quotation linked to this request yet.</p>
            <a href="{{ route('admin.quotations.create', ['from_quote' => $quote->id]) }}"
                style="display:inline-block;padding:10px 20px;background:linear-gradient(135deg,#16456e,#165752);color:#fff;border-radius:10px;font-weight:700;font-size:0.88rem;text-decoration:none;">
                <i class="fa fa-plus mr-2"></i>Create Quotation from this Request
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
