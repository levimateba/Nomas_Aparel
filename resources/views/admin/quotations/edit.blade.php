@extends('layouts.admin')
@section('title', 'Edit Quotation')
@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="margin:0;">Edit Quotation: {{ $quotation->quotation_number }}</h2>
            @if($quotation->quote_id)
                <p style="margin:4px 0 0;font-size:0.88rem;color:#607d8b;">
                    Linked to <a href="{{ route('admin.quotes.show', $quotation->quote_id) }}" style="color:#2196f3;font-weight:600;text-decoration:none;">Quote Request #{{ $quotation->quote_id }}</a>
                </p>
            @endif
        </div>
        <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary" style="text-decoration:none;">Back to List</a>
    </div>

    <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}">
        @csrf
        @method('PUT')
        @include('admin.quotations._form')
        <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
            @if($quotation->quote_id)
                <a href="{{ route('admin.quotes.show', $quotation->quote_id) }}" class="btn btn-secondary" style="text-decoration:none;">View Quote Request</a>
            @endif
            <button type="submit" class="btn">Update Quotation</button>
        </div>
    </form>
@endsection
