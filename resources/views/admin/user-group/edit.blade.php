@extends('layouts.admin')
@section('title','Edit User Group')
@section('content')
    <div class="page-head">
        <h2>Edit User Group</h2>
        <a class="btn btn-secondary" href="{{ route('admin.user-groups.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.user-groups.update', $group) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $group->name) }}" required>
            <button type="submit">Update group</button>
        </form>
    </div>
@endsection
