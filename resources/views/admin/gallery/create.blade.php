@extends('layouts.admin')
@section('title','Add Gallery Image')
@section('content')
    <h2>Add Gallery Image</h2>
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
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Title <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Project Workshops" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;transition:border-color 0.2s;">
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}" list="category-suggestions"
                        placeholder="e.g. Workshop, Product, Event"
                        style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;transition:border-color 0.2s;">
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
                    <small style="color:#888;font-size:0.82rem;">Choose a category or type your own.</small>
                </div>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Short Description</label>
                <input type="text" name="short_description" value="{{ old('short_description') }}"
                    placeholder="One-line caption shown beneath the title (max 255 chars)"
                    maxlength="255"
                    style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;">
                <small style="color:#888;font-size:0.82rem;">A brief caption or subtitle displayed on the gallery card.</small>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Full Description</label>
                <textarea name="description" rows="4" placeholder="Detailed description of this image/event (shown on expand)..."
                    style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;outline:none;box-sizing:border-box;resize:vertical;">{{ old('description') }}</textarea>
                <small style="color:#888;font-size:0.82rem;">Optional. Detailed context shown when the visitor expands the gallery card.</small>
            </div>
            <div style="margin-bottom:22px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;color:#16456e;">Image <span style="color:#dc3545;">*</span></label>
                <input type="file" name="image" accept="image/*" required
                    style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.97rem;box-sizing:border-box;background:#fafbfc;">
                <small style="color:#888;font-size:0.82rem;">JPEG, PNG, GIF — max 4MB. Recommended: 800×600px or wider.</small>
            </div>
            <div style="display:flex;gap:12px;">
                <button type="submit">Save Image</button>
                <a href="{{ route('admin.gallery.index') }}" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
