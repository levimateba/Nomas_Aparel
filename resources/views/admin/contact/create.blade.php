@extends('layouts.admin')
@section('title','Create Contact')
@section('content')
    <h2>Create Contact</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.contact.store') }}">
            @csrf
            <label>Label</label>
            <input type="text" name="label" value="{{ old('label') }}" required>
            <label>Value</label>
            <input type="text" name="value" value="{{ old('value') }}" required>
            <label>Type</label>
            <select name="type" required>
                <option value="text" {{ old('type')=='text' ? 'selected' : '' }}>Text</option>
                <option value="email" {{ old('type')=='email' ? 'selected' : '' }}>Email</option>
                <option value="phone" {{ old('type')=='phone' ? 'selected' : '' }}>Phone</option>
                <option value="address" {{ old('type')=='address' ? 'selected' : '' }}>Address</option>
            </select>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection