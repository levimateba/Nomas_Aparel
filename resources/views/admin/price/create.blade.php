@extends('layouts.admin')
@section('title','Create Pricing')
@section('content')
    <h2>Create Pricing</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.price.store') }}">
            @csrf
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description') }}</textarea>
            <label>Amount (Kshs.)</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required>
            <label>Billing Period</label>
            <input type="text" name="billing_period" value="{{ old('billing_period','monthly') }}" required>
            
            <label style="margin-top: 20px;">Features (one per line)</label>
            <textarea name="features_text" rows="5" placeholder="Maximum Page: 5&#10;CMS: No&#10;Hosting: 1 Year&#10;Forms: No&#10;Social Media Integration: No">{{ old('features_text') }}</textarea>
            <small style="display: block; color: #666; margin-top: 5px;">Enter features as key: value pairs, one per line. Example: "CMS: Yes"</small>
            
            <label style="margin-top: 15px;"><input type="checkbox" name="featured" {{ old('featured') ? 'checked' : '' }}> Featured Plan (highlight this plan)</label>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection