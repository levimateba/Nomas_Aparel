@extends('layouts.admin')
@section('title','Add Team Member')
@section('content')
    <h2>Add Team Member</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.team.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <label>Position</label>
            <input type="text" name="position" value="{{ old('position') }}" required>
            <label>Short Description</label>
            <textarea name="description" rows="5" required>{{ old('description') }}</textarea>
            <label>Photo</label>
            <input type="file" name="image" accept="image/*">
            <label>Facebook Link (optional)</label>
            <input type="url" name="facebook_url" value="{{ old('facebook_url') }}">
            <label>Twitter Link (optional)</label>
            <input type="url" name="twitter_url" value="{{ old('twitter_url') }}">
            <label>LinkedIn Link (optional)</label>
            <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}">
            <label>Instagram Link (optional)</label>
            <input type="url" name="instagram_url" value="{{ old('instagram_url') }}">
            <button type="submit">Save</button>
        </form>
    </div>
@endsection
