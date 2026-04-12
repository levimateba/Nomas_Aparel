@extends('layouts.admin')

@section('title', 'Enquiry #' . $enquiry->id)

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
    @media (max-width: 680px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="{{ route('admin.enquiries.index') }}" style="color:#16456e;text-decoration:none;font-weight:600;font-size:0.9rem;">
        &larr; Back to Enquiries
    </a>
    <span style="color:#ccc;">|</span>
    <h2 style="margin:0;">Enquiry #{{ $enquiry->id }}</h2>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.45fr) minmax(0,0.8fr);gap:20px;align-items:start;">
    <div>
        <div class="card">
            <h3 style="margin:0 0 18px;color:var(--primary-dark);">Sender Information</h3>
            <div class="detail-grid">
                <div class="detail-field">
                    <label>Full Name</label>
                    <p>{{ $enquiry->name }}</p>
                </div>
                <div class="detail-field">
                    <label>Email</label>
                    <p><a href="mailto:{{ $enquiry->email }}" style="color:var(--primary-green);">{{ $enquiry->email }}</a></p>
                </div>
                <div class="detail-field">
                    <label>Subject</label>
                    <p>{{ $enquiry->subject }}</p>
                </div>
                <div class="detail-field">
                    <label>Submitted</label>
                    <p>{{ $enquiry->created_at->format('F j, Y \a\t H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin:0 0 14px;color:var(--primary-dark);">Message</h3>
            <p style="color:#444;line-height:1.8;margin:0;white-space:pre-line;">{{ $enquiry->message }}</p>
        </div>
    </div>

    <div>
        <div class="card" style="background:rgba(22,69,110,0.04);border:1.5px solid rgba(22,69,110,0.1);">
            <h4 style="margin:0 0 10px;color:var(--primary-dark);font-size:0.9rem;">Quick Actions</h4>
            <a href="mailto:{{ $enquiry->email }}?subject=Re: {{ rawurlencode($enquiry->subject) }}"
                style="display:block;padding:10px 14px;background:#fff;border:1.5px solid #e0e7ef;border-radius:10px;color:#16456e;font-weight:600;font-size:0.88rem;text-decoration:none;margin-bottom:8px;">
                <i class="fa fa-envelope mr-2"></i>Reply via Email
            </a>
            <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Permanently delete this enquiry?')">
                @csrf @method('DELETE')
                <button type="submit" style="width:100%;padding:10px 14px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border:none;border-radius:10px;font-weight:600;font-size:0.88rem;cursor:pointer;">
                    <i class="fa fa-trash mr-2"></i>Delete Enquiry
                </button>
            </form>
        </div>
    </div>
</div>
@endsection