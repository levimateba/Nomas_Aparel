@extends('layouts.storefront')

@section('title', 'Contact')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:20px;max-width:720px;margin:0 auto;">
        <h2 style="margin-top:0;">Contact us</h2>
        <p style="color:#6b7280;">Send a message and it will appear in Admin → Enquiries.</p>
        <form method="POST" action="{{ route('contact.submit') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;margin:6px 0 12px;">

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;margin:6px 0 12px;">

            <label>Subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;margin:6px 0 12px;">

            <label>Message</label>
            <textarea name="message" rows="5" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;margin:6px 0 14px;">{{ old('message') }}</textarea>

            <button class="btn btn-primary" type="submit">Send enquiry</button>
        </form>
    </div>
</div>
@endsection
