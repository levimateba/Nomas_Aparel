@extends('layouts.admin')
@section('title','Add Permission')
@section('content')
    <h2>Add Permission</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.permissions.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description') }}</textarea>
            <button type="submit">Save Permission</button>
        </form>
    </div>
@endsection
