@extends('layouts.admin')
@section('title', 'Permissions')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Admin</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Permissions</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Permission Catalogue</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Official access controls used across POS, inventory, people, and admin.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.permissions.sync') }}">
            @csrf
            <button type="submit" class="ta-btn">Sync catalogue</button>
        </form>
        <form method="POST" action="{{ route('admin.permissions.prune') }}" onsubmit="return confirm('Remove unused / legacy permissions from the database?')">
            @csrf
            <button type="submit" class="ta-btn-outline">Remove unused</button>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page" x-data="{ q: '' }">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">In catalogue</div>
                <div class="ta-kpi-value">{{ number_format($stats['catalog']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Groups</div>
                <div class="ta-kpi-value">{{ number_format($stats['groups']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">In database</div>
                <div class="ta-kpi-value">{{ number_format($stats['in_db']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-warning">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Unused / legacy</div>
                <div class="ta-kpi-value">{{ number_format($stats['orphans']) }}</div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-brand-200 bg-brand-50/60 px-4 py-3 text-sm text-brand-900 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200">
        Permissions are defined in <code class="rounded bg-white/70 px-1.5 py-0.5 dark:bg-black/20">config/permissions.php</code>.
        Use <strong>Sync catalogue</strong> to add missing keys, and <strong>Remove unused</strong> to delete legacy permissions.
    </div>

    <div class="ta-toolbar">
        <div class="ta-field" style="flex:2;">
            <label>Search catalogue</label>
            <input type="search" class="ta-input" placeholder="Filter by label, key, or description…" x-model.debounce.150ms="q">
        </div>
    </div>

    @foreach($grouped as $group => $items)
        @php
            $groupSearch = strtolower($group.' '.collect($items)->map(fn ($i) => $i['meta']['label'].' '.$i['name'].' '.($i['meta']['description'] ?? ''))->implode(' '));
        @endphp
        <section class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                 data-search="{{ e($groupSearch) }}"
                 x-show="!q || ($el.dataset.search || '').includes(q.toLowerCase())">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $group }}</h2>
                <p class="text-sm text-gray-500">{{ count($items) }} permission{{ count($items) === 1 ? '' : 's' }}</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($items as $item)
                    @php
                        $rowSearch = strtolower($item['meta']['label'].' '.$item['name'].' '.($item['meta']['description'] ?? ''));
                    @endphp
                    <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-start sm:justify-between"
                         data-search="{{ e($rowSearch) }}"
                         x-show="!q || ($el.dataset.search || '').includes(q.toLowerCase())">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="font-semibold text-gray-800 dark:text-white/90">{{ $item['meta']['label'] }}</div>
                                @if($item['in_db'])
                                    <span class="ta-pill ta-pill-success">Synced</span>
                                @else
                                    <span class="ta-pill ta-pill-warning">Missing in DB</span>
                                @endif
                            </div>
                            <code class="mt-1 block font-mono text-xs text-brand-700 dark:text-brand-400">{{ $item['name'] }}</code>
                            <p class="mt-1 text-sm text-gray-500">{{ $item['meta']['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    @if($orphans->isNotEmpty())
        <section class="rounded-2xl border border-warning-200 bg-warning-50/40 dark:border-warning-500/30 dark:bg-warning-500/5">
            <div class="border-b border-warning-200/70 px-5 py-4 dark:border-warning-500/20">
                <h2 class="text-base font-semibold text-warning-900 dark:text-warning-200">Unused / legacy permissions</h2>
                <p class="text-sm text-warning-800 dark:text-warning-300">Not in the official catalogue. Safe to remove.</p>
            </div>
            <div class="divide-y divide-warning-100 dark:divide-warning-500/10">
                @foreach($orphans as $permission)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-800 dark:text-white/90">{{ $permission->name }}</div>
                            <div class="text-sm text-gray-500">{{ $permission->description ?: 'No description' }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this permission?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
