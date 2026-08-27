@extends('layouts.admin')
@section('title','Add Permission')
@section('content')
    <div class="page-head">
        <h2>Add Permission</h2>
        <a class="btn btn-secondary" href="{{ route('admin.permissions.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.permissions.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description') }}</textarea>
            <button type="submit">Save permission</button>
        </form>
    </div>
@endsection
