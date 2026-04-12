@extends('layouts.admin')
@section('title','Edit Professional')
@section('content')
    <h2>Edit Professional</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.team.update', $member) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $member->name) }}" required>
            <label>Position</label>
            <input type="text" name="position" value="{{ old('position', $member->position) }}" required>
            <label>Short Description</label>
            <textarea name="description" rows="5" required>{{ old('description', $member->description) }}</textarea>
            @if($member->image)
                <div style="margin: 10px 0;">
                    <img src="{{ $member->image }}" alt="{{ $member->name }}" style="width:180px;height:180px;object-fit:cover;border-radius:12px;">
                </div>
            @endif
            <input type="file" name="image" accept="image/*">
            <label>Facebook Link (optional)</label>
            <input type="url" name="facebook_url" value="{{ old('facebook_url', $member->facebook_url) }}">
            <label>Twitter Link (optional)</label>
            <input type="url" name="twitter_url" value="{{ old('twitter_url', $member->twitter_url) }}">
            <label>LinkedIn Link (optional)</label>
            <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $member->linkedin_url) }}">
            <label>Instagram Link (optional)</label>
            <input type="url" name="instagram_url" value="{{ old('instagram_url', $member->instagram_url) }}">
            <button type="submit">Update</button>
        </form>
    </div>
@endsection
