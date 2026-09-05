@extends('layouts.admin')
@section('title', 'Add User Group')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.user-groups.index') }}" class="hover:text-brand-500">User Groups</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">New</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Add User Group</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a team or department group for staff.</p>
    </div>
    <a href="{{ route('admin.user-groups.index') }}" class="ta-btn-outline">Back</a>
</div>
@endsection

@section('content')
<div class="ta-page" style="max-width:720px;">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Group details</h2>
        </div>
        <form method="POST" action="{{ route('admin.user-groups.store') }}" class="space-y-5 p-5">
            @csrf
            <div class="ta-field">
                <label for="name">Name *</label>
                <input id="name" type="text" name="name" class="ta-input" required maxlength="255" value="{{ old('name') }}" placeholder="e.g. Shop Floor">
            </div>
            <div class="ta-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" class="ta-input" maxlength="1000" placeholder="Optional notes">{{ old('description') }}</textarea>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="ta-btn">Save group</button>
                <a href="{{ route('admin.user-groups.index') }}" class="ta-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
