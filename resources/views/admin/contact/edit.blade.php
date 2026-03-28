@extends('layouts.admin')
@section('title','Edit Contact')
@section('content')
    <h2>Edit Contact</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.contact.update', $contact) }}">
            @csrf
            @method('PUT')
            <label>Label</label>
            <input type="text" name="label" value="{{ old('label', $contact->label) }}" required>
            <label>Value</label>
            <input type="text" name="value" value="{{ old('value', $contact->value) }}" required>
            <label>Type</label>
            <select name="type" required>
                <option value="text" {{ old('type', $contact->type)=='text' ? 'selected' : '' }}>Text</option>
                <option value="email" {{ old('type', $contact->type)=='email' ? 'selected' : '' }}>Email</option>
                <option value="phone" {{ old('type', $contact->type)=='phone' ? 'selected' : '' }}>Phone</option>
                <option value="address" {{ old('type', $contact->type)=='address' ? 'selected' : '' }}>Address</option>
            </select>
            <button type="submit">Update</button>
        </form>
    </div>
@endsection