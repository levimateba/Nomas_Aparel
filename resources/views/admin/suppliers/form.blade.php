@extends('layouts.admin')
@section('title', $supplier->exists ? 'Edit supplier' : 'Add supplier')
@section('heading', $supplier->exists ? 'Edit supplier' : 'Add supplier')

@section('content')
<div class="ta-page" style="max-width:720px;">
    <div class="ta-table-card" style="padding:16px;">
        <form method="POST" action="{{ $supplier->exists ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
            @csrf
            @if($supplier->exists) @method('PUT') @endif
            <div class="admin-form-grid">
                <div class="ta-field">
                    <label>Name *</label>
                    <input class="ta-input" type="text" name="name" value="{{ old('name', $supplier->name) }}" required>
                </div>
                <div class="ta-field">
                    <label>Company</label>
                    <input class="ta-input" type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}">
                </div>
                <div class="ta-field">
                    <label>Phone</label>
                    <input class="ta-input" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
                </div>
                <div class="ta-field">
                    <label>Email</label>
                    <input class="ta-input" type="email" name="email" value="{{ old('email', $supplier->email) }}">
                </div>
                <div class="ta-field">
                    <label>Tax PIN</label>
                    <input class="ta-input" type="text" name="tax_pin" value="{{ old('tax_pin', $supplier->tax_pin) }}">
                </div>
                <div class="ta-field">
                    <label>Status</label>
                    <select class="ta-select" name="is_active">
                        <option value="1" @selected(old('is_active', $supplier->is_active ? '1' : '0') === '1')>Active</option>
                        <option value="0" @selected(old('is_active', $supplier->is_active ? '1' : '0') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="ta-field" style="grid-column:1/-1;">
                    <label>Address</label>
                    <input class="ta-input" type="text" name="address" value="{{ old('address', $supplier->address) }}">
                </div>
                <div class="ta-field" style="grid-column:1/-1;">
                    <label>Notes</label>
                    <textarea class="ta-input" name="notes" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
                </div>
            </div>
            <div class="row-actions" style="margin-top:16px;display:flex;gap:8px;">
                <button type="submit" class="ta-btn">Save</button>
                <a href="{{ route('admin.suppliers.index') }}" class="ta-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
