@extends('layouts.admin')
@section('title', $supplier->exists ? 'Edit supplier' : 'Add supplier')
@section('heading', $supplier->exists ? 'Edit supplier' : 'Add supplier')

@section('content')
<div class="card" style="padding:16px;max-width:720px;">
    <form method="POST" action="{{ $supplier->exists ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
        @csrf
        @if($supplier->exists) @method('PUT') @endif
        <div class="admin-form-grid">
            <div>
                <label>Name *</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required>
            </div>
            <div>
                <label>Company</label>
                <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}">
            </div>
            <div>
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}">
            </div>
            <div>
                <label>Tax PIN</label>
                <input type="text" name="tax_pin" value="{{ old('tax_pin', $supplier->tax_pin) }}">
            </div>
            <div>
                <label>Status</label>
                <select name="is_active">
                    <option value="1" @selected(old('is_active', $supplier->is_active ? '1' : '0') === '1')>Active</option>
                    <option value="0" @selected(old('is_active', $supplier->is_active ? '1' : '0') === '0')>Inactive</option>
                </select>
            </div>
            <div style="grid-column:1/-1;">
                <label>Address</label>
                <input type="text" name="address" value="{{ old('address', $supplier->address) }}">
            </div>
            <div style="grid-column:1/-1;">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
            </div>
        </div>
        <div class="row-actions">
            <button type="submit" class="btn">Save</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn" style="background:#f3f4f6;color:#111;">Cancel</a>
        </div>
    </form>
</div>
@endsection
