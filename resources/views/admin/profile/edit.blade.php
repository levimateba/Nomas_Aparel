@extends('layouts.admin')
@section('title', 'Profile')

@section('page_header')
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">My Profile</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">My Profile</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your name, email, and password for this account.</p>
    </div>
</div>
@endsection

@section('content')
@php
    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
    $roleName = $user->role?->name ?? ($user->isAdminUser() ? 'Admin' : 'Staff');
@endphp
<div class="ta-page">
    <div class="grid gap-5 lg:grid-cols-[280px_minmax(0,1fr)]">
        <aside class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#111827] text-2xl font-bold text-brand-500">
                    {{ $initial }}
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-800 dark:text-white/90">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>
                <span class="mt-3 inline-flex rounded-full bg-[#f6f0df] px-3 py-1 text-xs font-bold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                    {{ $roleName }}
                </span>
            </div>
            <dl class="mt-6 space-y-3 border-t border-gray-100 pt-5 text-sm dark:border-gray-800">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-500">Member since</dt>
                    <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $user->created_at?->format('d M Y') ?: '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-gray-500">User ID</dt>
                    <dd class="font-semibold text-gray-800 dark:text-white/90">#{{ $user->id }}</dd>
                </div>
            </dl>
        </aside>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Account details</h3>
            <p class="mt-1 text-sm text-gray-500">Leave password blank to keep your current password.</p>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="ta-field">
                    <label for="name">Full name</label>
                    <input id="name" class="ta-input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                <div class="ta-field">
                    <label for="email">Email</label>
                    <input id="email" class="ta-input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="ta-field">
                        <label for="password">New password</label>
                        <input id="password" class="ta-input" type="password" name="password" autocomplete="new-password" placeholder="Optional">
                        @error('password')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="ta-field">
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" class="ta-input" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Optional">
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <button class="ta-btn" type="submit">Save profile</button>
                    <a href="{{ route('admin.dashboard') }}" class="ta-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
