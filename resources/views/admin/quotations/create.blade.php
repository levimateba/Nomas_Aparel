@extends('layouts.admin')
@section('title', 'Create Quotation')
@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="margin:0;">Create Quotation</h2>
            @if(isset($fromQuote))
                <p style="margin:4px 0 0;color:#607d8b;font-size:0.88rem;">
                    Pre-filled from Quote Request <strong>#{{ $fromQuote->id }}</strong> — {{ $fromQuote->name }}
                </p>
            @endif
        </div>
        <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary" style="text-decoration:none;">Back to List</a>
    </div>

    <form method="POST" action="{{ route('admin.quotations.store') }}">
        @csrf
        @if(isset($fromQuote))
            <input type="hidden" name="quote_id" value="{{ $fromQuote->id }}">
        @endif
        @include('admin.quotations._form')
        <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
            @if(isset($fromQuote))
                <a href="{{ route('admin.quotes.show', $fromQuote) }}" class="btn btn-secondary" style="text-decoration:none;">Back to Quote Request</a>
            @endif
            <button type="submit" class="btn">Save Quotation</button>
        </div>
    </form>
@endsection
