@extends('layouts.admin')
@section('title','Edit User')
@section('heading', 'Edit User')
@section('subheading', 'Update account details, photo, and print the job card.')

@section('content')
    <div class="page-head">
        <h2>Edit User</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="btn" href="{{ route('admin.users.print-card', $user) }}" target="_blank" rel="noopener">Print Job Card</a>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Back</a>
        </div>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="admin-form-grid">
                <div style="grid-column:1 / -1;">
                    <label>Profile picture</label>
                    <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                        <div id="avatar-preview" style="width:88px;height:88px;border-radius:50%;background:#f3f4f6;border:1px solid #e5e7eb;display:grid;place-items:center;overflow:hidden;color:#9ca3af;font-weight:800;">
                            @if($user->avatarUrl())
                                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ $user->initials() }}
                            @endif
                        </div>
                        <div style="flex:1;min-width:220px;">
                            <input id="avatar-input" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <p class="muted" style="margin:6px 0 0;">JPG, PNG or WebP up to 2MB.</p>
                            @if($user->avatarUrl())
                                <label style="display:inline-flex;align-items:center;gap:8px;margin-top:8px;font-size:13px;">
                                    <input type="checkbox" name="remove_avatar" value="1"> Remove current photo
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div>
                    <label>Job title</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="e.g. Cashier, Store Manager">
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>
                <div>
                    <label>Employee code</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $user->employee_code) }}">
                </div>
                <div>
                    <label>New password</label>
                    <input type="password" name="password">
                </div>
                <div>
                    <label>Confirm password</label>
                    <input type="password" name="password_confirmation">
                </div>
                <div>
                    <label>Role</label>
                    <select name="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>User group</label>
                    <select name="user_group_id">
                        <option value="">None</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('user_group_id', $user->user_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit">Update user</button>
        </form>
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
