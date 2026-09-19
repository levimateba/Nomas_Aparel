@extends('layouts.admin')
@section('title','Add User')
@section('heading', 'Add User')
@section('subheading', 'Create a staff account with role, photo, and job card details.')

@section('content')
<div class="ta-page" style="max-width:860px;">
    <div class="page-head">
        <h2>Add User</h2>
        <a class="ta-btn-outline" href="{{ route('admin.users.index') }}">Back</a>
    </div>

    <div class="ta-table-card" style="padding:16px;">
        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="admin-form-grid">
                <div class="ta-field" style="grid-column:1 / -1;">
                    <label>Profile picture</label>
                    <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                        <div id="avatar-preview" style="width:88px;height:88px;border-radius:50%;background:#f3f4f6;border:1px solid #e5e7eb;display:grid;place-items:center;overflow:hidden;color:#9ca3af;font-weight:800;">?</div>
                        <div style="flex:1;min-width:220px;">
                            <input id="avatar-input" class="ta-input" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <p class="text-xs text-gray-500 dark:text-gray-400" style="margin:6px 0 0;">JPG, PNG or WebP up to 2MB. Shown on the printable job card.</p>
                            @error('avatar')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                <div class="ta-field">
                    <label>Name *</label>
                    <input class="ta-input" type="text" name="name" value="{{ old('name') }}" required>
                    @error('name')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Email *</label>
                    <input class="ta-input" type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Job title</label>
                    <input class="ta-input" type="text" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Cashier, Store Manager">
                    @error('job_title')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Phone</label>
                    <input class="ta-input" type="text" name="phone" value="{{ old('phone') }}" placeholder="Optional">
                    @error('phone')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Employee code</label>
                    <input class="ta-input" type="text" name="employee_code" value="{{ old('employee_code') }}" placeholder="Auto-generated if blank">
                    @error('employee_code')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Role *</label>
                    <select class="ta-select" name="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Password *</label>
                    <input class="ta-input" type="password" name="password" required autocomplete="new-password">
                    @error('password')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="ta-field">
                    <label>Confirm password *</label>
                    <input class="ta-input" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
                <div class="ta-field">
                    <label>User group</label>
                    <select class="ta-select" name="user_group_id">
                        <option value="">None</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected((string) old('user_group_id') === (string) $group->id)>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('user_group_id')<p class="text-xs text-error-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="row-actions" style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;">
                <button type="submit" class="ta-btn">Save user</button>
                <a href="{{ route('admin.users.index') }}" class="ta-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('avatar-input')?.addEventListener('change', function () {
    const box = document.getElementById('avatar-preview');
    const file = this.files?.[0];
    if (!box || !file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        box.innerHTML = '';
        const img = document.createElement('img');
        img.src = e.target.result;
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        box.appendChild(img);
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
