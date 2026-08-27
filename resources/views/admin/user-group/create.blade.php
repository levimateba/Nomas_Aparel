@extends('layouts.admin')
@section('title','Add User Group')
@section('content')
    <div class="page-head">
        <h2>Add User Group</h2>
        <a class="btn btn-secondary" href="{{ route('admin.user-groups.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.user-groups.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <button type="submit">Save group</button>
        </form>
    </div>
@endsection
