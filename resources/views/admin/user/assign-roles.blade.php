@extends('layouts.admin')
@section('title', 'Assign Roles')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.users.index') }}" class="hover:text-brand-500">Users</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Assign roles</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Assign Roles</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose which roles {{ $user->name }} should have.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.users.edit', $user) }}" class="ta-btn-outline">Edit user</a>
        <a href="{{ route('admin.users.index') }}" class="ta-btn-outline">Back to Users</a>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3">
                @if($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="" class="h-14 w-14 rounded-2xl object-cover">
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-lg font-bold text-brand-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="truncate text-base font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</div>
                    <div class="truncate text-sm text-gray-500">{{ $user->email }}</div>
                </div>
            </div>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Primary role</dt>
                    <dd class="font-medium text-gray-800 dark:text-white/90">{{ $user->role?->name ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Group</dt>
                    <dd class="font-medium text-gray-800 dark:text-white/90">{{ $user->group?->name ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Assigned roles</dt>
                    <dd class="font-medium text-gray-800 dark:text-white/90">{{ count($userRoles) }}</dd>
                </div>
            </dl>
            <p class="mt-4 rounded-xl bg-brand-50/70 px-3 py-2 text-xs text-brand-900 dark:bg-brand-500/10 dark:text-brand-200">
                The first selected role becomes the primary role used for dashboards and login routing.
            </p>
        </div>

        <div class="xl:col-span-2">
            <form method="POST" action="{{ route('admin.users.update-roles', $user) }}" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                @csrf
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Available roles</h2>
                    <p class="mt-1 text-sm text-gray-500">Select one or more roles for this staff account.</p>
                </div>
                <div class="grid grid-cols-1 gap-3 p-5 md:grid-cols-2">
                    @forelse($roles as $role)
                        <label class="flex cursor-pointer gap-3 rounded-2xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/5 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60 dark:has-[:checked]:bg-brand-500/10">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"
                                   @checked(in_array($role->id, old('roles', $userRoles)))>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-gray-800 dark:text-white/90">{{ $role->name }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500">{{ $role->description ?: ($role->slug ?: 'No description') }}</span>
                                <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                    {{ $role->permissions_count }} permission{{ $role->permissions_count === 1 ? '' : 's' }}
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 md:col-span-2">No roles available. <a class="font-semibold text-brand-600" href="{{ route('admin.roles.create') }}">Create a role</a>.</p>
                    @endforelse
                </div>
                <div class="flex flex-wrap gap-2 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                    <button type="submit" class="ta-btn">Update Roles</button>
                    <a href="{{ route('admin.users.index') }}" class="ta-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
