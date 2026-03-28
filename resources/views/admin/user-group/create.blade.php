@extends('layouts.admin')
@section('title','Add User Group')
@section('content')
    <h2>Add User Group</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.user-groups.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description') }}</textarea>
            <button type="submit">Save Group</button>
        </form>
    </div>
@endsection
