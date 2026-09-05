@extends('layouts.admin')
@section('title', 'Add Role')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.roles.index') }}" class="hover:text-brand-500">Roles</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">New</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Add Role</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a role, then assign permissions from the catalogue.</p>
    </div>
    <div>
        <a href="{{ route('admin.roles.index') }}" class="ta-btn-outline">Back to Roles</a>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page" style="max-width:760px;">
    @if ($errors->any())
        <div class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Role details</h2>
            <p class="mt-1 text-sm text-gray-500">A unique slug is generated from the name automatically.</p>
        </div>
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5 p-5">
            @csrf
            <div class="ta-field">
                <label for="name">Name *</label>
                <input id="name" type="text" name="name" class="ta-input" required maxlength="255"
                       value="{{ old('name') }}" placeholder="e.g. Shift Supervisor">
            </div>
            <div class="ta-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="ta-input" maxlength="1000"
                          placeholder="What this role can do in the store…">{{ old('description') }}</textarea>
            </div>

            <div class="rounded-xl border border-brand-200 bg-brand-50/60 px-4 py-3 text-sm text-brand-900 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200">
                After saving, open <strong>Assign Permissions</strong> to grant POS, inventory, reports, and admin access.
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="ta-btn">Save role</button>
                <a href="{{ route('admin.roles.index') }}" class="ta-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
