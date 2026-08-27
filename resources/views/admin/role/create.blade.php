@extends('layouts.admin')
@section('title', 'Add Role')
@section('content')
    <div class="page-head">
        <h2>Add Role</h2>
        <a class="btn btn-secondary" href="{{ route('admin.roles.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description') }}</textarea>
            <p class="muted">Assign permissions after the role is created.</p>
            <button type="submit">Save role</button>
        </form>
    </div>
@endsection
