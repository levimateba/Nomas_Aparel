@extends('layouts.admin')
@section('title', 'Assign Permissions')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.roles.index') }}" class="hover:text-brand-500">Roles</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Permissions</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Assign Permissions</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Role: <strong class="text-gray-700 dark:text-gray-200">{{ $role->name }}</strong> · {{ count($rolePermissions) }} currently assigned</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.roles.edit', $role) }}" class="ta-btn-outline">Edit role</a>
        <a href="{{ route('admin.roles.index') }}" class="ta-btn-outline">Back to Roles</a>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page" x-data="{ q: '' }">
    <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <label class="flex items-center gap-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                    <input type="checkbox" id="select-all-permissions" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20">
                    Select all permissions
                </label>
                <div class="ta-field" style="min-width:260px;margin:0;">
                    <label class="sr-only">Search</label>
                    <input type="search" class="ta-input" placeholder="Search permissions…" x-model.debounce.150ms="q">
                </div>
            </div>
        </div>

        @foreach($permissionGroups as $group => $items)
            @php
                $groupSearch = strtolower($group.' '.collect($items)->map(fn ($meta, $name) => ($meta['label'] ?? '').' '.$name.' '.($meta['description'] ?? ''))->implode(' '));
            @endphp
            <section class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                     data-search="{{ e($groupSearch) }}"
                     x-show="!q || ($el.dataset.search || '').includes(q.toLowerCase())">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $group }}</h2>
                        <p class="text-sm text-gray-500">{{ count($items) }} permission(s)</p>
                    </div>
                    <button type="button" class="ta-btn-outline ta-btn-sm group-select" data-group="{{ \Illuminate\Support\Str::slug($group) }}">Toggle group</button>
                </div>
                <div class="grid grid-cols-1 gap-2 p-4 md:grid-cols-2">
                    @foreach($items as $name => $meta)
                        @if($permission = $permissionsByName->get($name))
                            @php $rowSearch = strtolower(($meta['label'] ?? '').' '.$name.' '.($meta['description'] ?? '')); @endphp
                            <label class="flex gap-3 rounded-xl border border-gray-100 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/5 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 dark:has-[:checked]:bg-brand-500/10"
                                   data-group="{{ \Illuminate\Support\Str::slug($group) }}"
                                   data-search="{{ e($rowSearch) }}"
                                   x-show="!q || ($el.dataset.search || '').includes(q.toLowerCase())">
                                <input class="permission-checkbox mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"
                                       type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                       @checked(in_array($permission->id, old('permissions', $rolePermissions)))>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-gray-800 dark:text-white/90">{{ $meta['label'] }}</span>
                                    <span class="block font-mono text-xs text-brand-600 dark:text-brand-400">{{ $name }}</span>
                                    <span class="mt-0.5 block text-xs text-gray-500">{{ $meta['description'] }}</span>
                                </span>
                            </label>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach

        @php
            $catalogNames = array_keys(config('permissions.catalog', []));
            $extraPermissions = $permissions->reject(fn ($permission) => in_array($permission->name, $catalogNames, true));
        @endphp

        @if($extraPermissions->isNotEmpty())
            <section class="rounded-2xl border border-warning-200 bg-warning-50/30 dark:border-warning-500/30 dark:bg-warning-500/5">
                <div class="border-b border-warning-200/70 px-5 py-4 dark:border-warning-500/20">
                    <h2 class="text-base font-semibold text-warning-900 dark:text-warning-200">Legacy / unused permissions</h2>
                    <p class="text-sm text-warning-800 dark:text-warning-300">Prefer removing these from <a class="underline" href="{{ route('admin.permissions.index') }}">Permissions</a>.</p>
                </div>
                <div class="grid grid-cols-1 gap-2 p-4 md:grid-cols-2">
                    @foreach($extraPermissions as $permission)
                        <label class="flex gap-3 rounded-xl border border-warning-100 bg-white px-4 py-3 dark:border-warning-500/20 dark:bg-transparent">
                            <input class="permission-checkbox mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"
                                   type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                   @checked(in_array($permission->id, old('permissions', $rolePermissions)))>
                            <span>
                                <span class="block text-sm font-semibold text-gray-800 dark:text-white/90">{{ $permission->name }}</span>
                                <span class="block font-mono text-xs text-gray-500">{{ $permission->slug }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="sticky bottom-3 z-10 flex flex-wrap gap-2 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-theme-md backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
            <button type="submit" class="ta-btn">Update Permissions</button>
            <a class="ta-btn-outline" href="{{ route('admin.roles.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const selectAll = document.getElementById('select-all-permissions');
        const checkboxes = Array.from(document.querySelectorAll('.permission-checkbox'));
        if (!selectAll || checkboxes.length === 0) return;

        function syncSelectAll() {
            const checkedCount = checkboxes.filter((cb) => cb.checked).length;
            selectAll.checked = checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        selectAll.addEventListener('change', () => {
            checkboxes.forEach((cb) => { cb.checked = selectAll.checked; });
            selectAll.indeterminate = false;
        });
        checkboxes.forEach((cb) => cb.addEventListener('change', syncSelectAll));
        document.querySelectorAll('.group-select').forEach((button) => {
            button.addEventListener('click', () => {
                const group = button.dataset.group;
                const groupBoxes = checkboxes.filter((cb) => cb.closest('[data-group]')?.dataset.group === group);
                const allChecked = groupBoxes.length > 0 && groupBoxes.every((cb) => cb.checked);
                groupBoxes.forEach((cb) => { cb.checked = !allChecked; });
                syncSelectAll();
            });
        });
        syncSelectAll();
    })();
</script>
@endpush
