@extends('layouts.admin')
@section('title','Edit Gallery Image')
@section('content')
    <h2>Edit Gallery Image</h2>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <form method="POST" action="{{ route('admin.gallery.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Title <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $item->title) }}" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Category</label>
                    <input type="text" name="category" value="{{ old('category', $item->category) }}" list="category-suggestions"
                        placeholder="e.g. Workshop, Product, Event"
                        style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;">
                    <datalist id="category-suggestions">
                        <option value="Workshop">
                        <option value="Product Launch">
                        <option value="Event">
                        <option value="Team">
                        <option value="Infrastructure">
                        <option value="Client Visit">
                        <option value="Training">
                        <option value="Community">
                        <option value="Award">
                        <option value="Other">
                    </datalist>
                </div>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Short Description</label>
                <input type="text" name="short_description" value="{{ old('short_description', $item->short_description) }}"
                    placeholder="One-line caption shown beneath the title"
                    maxlength="255"
                    style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Full Description</label>
                <textarea name="description" rows="4"
                    placeholder="Detailed description (shown on expand)..."
                    style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;resize:vertical;">{{ old('description', $item->description) }}</textarea>
            </div>
            <div style="margin-bottom:22px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Current Image</label>
                <img src="{{ $item->image }}" alt="{{ $item->title }}"
                    style="width:200px;height:130px;object-fit:cover;border-radius:10px;box-shadow:0 4px 14px rgba(22,69,110,0.14);margin-bottom:12px;display:block;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Replace Image <small style="font-weight:400;color:#888;">(leave blank to keep current)</small></label>
                <input type="file" name="image" accept="image/*"
                    style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;box-sizing:border-box;background:#fafbfc;">
                <small style="color:#888;font-size:0.82rem;">JPEG, PNG, GIF — max 4MB.</small>
            </div>
            <div style="display:flex;gap:12px;">
                <button type="submit">Update Image</button>
                <a href="{{ route('admin.gallery.index') }}" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
