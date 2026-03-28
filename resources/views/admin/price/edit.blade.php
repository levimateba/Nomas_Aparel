@extends('layouts.admin')
@section('title','Edit Pricing')
@section('content')
    <h2>Edit Pricing</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.price.update', $price) }}">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $price->title) }}" required>
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $price->description) }}</textarea>
            <label>Amount (Kshs.)</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $price->amount) }}" required>
            <label>Billing Period</label>
            <input type="text" name="billing_period" value="{{ old('billing_period', $price->billing_period) }}" required>
            
            <label style="margin-top: 20px;">Features (one per line)</label>
            <textarea name="features_text" rows="5">{{ old('features_text', is_array($price->features) ? implode("\n", collect($price->features)->map(fn($v, $k) => "$k: $v")->values()->all()) : '') }}</textarea>
            <small style="display: block; color: #666; margin-top: 5px;">Enter features as key: value pairs, one per line. Example: "CMS: Yes"</small>
            
            <label style="margin-top: 15px;"><input type="checkbox" name="featured" {{ old('featured', $price->featured) ? 'checked' : '' }}> Featured Plan (highlight this plan)</label>
            <button type="submit">Update</button>
        </form>
    </div>
@endsection