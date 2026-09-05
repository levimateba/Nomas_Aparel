@extends('layouts.admin')
@section('title', 'Inventory Locations')

@php
    $filtersOpen = request()->anyFilled(['q', 'type', 'status']);
    $typeIcons = [
        'store' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6',
        'shop' => 'M3 10.5l9-7 9 7V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z',
        'warehouse' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'outlet' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1',
        'damaged' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
        'returns' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
        'other' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
    ];
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between" x-data="{ addOpen: false }">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Stock &amp; Catalog</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Locations</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Inventory Locations</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage main store, shop floor, warehouses, and outlets.</p>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-overview.index') }}" class="ta-btn-outline w-full justify-center sm:w-auto">Stock Overview</a>
        <button type="button" class="ta-btn w-full justify-center sm:w-auto" @click="addOpen = true">
            <span class="text-lg leading-none">+</span> Add Location
        </button>
    </div>

    <div x-show="addOpen" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-gray-900/50" @click="addOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900" @click.stop>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Add location</h3>
            <p class="mt-1 text-sm text-gray-500">Create a store, shop, warehouse, or other stock location.</p>
            <form method="POST" action="{{ route('admin.stock-locations.store') }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="ta-field sm:col-span-2">
                        <label>Name *</label>
                        <input type="text" name="name" class="ta-input" required placeholder="e.g. Main Store">
                    </div>
                    <div class="ta-field">
                        <label>Code *</label>
                        <input type="text" name="code" class="ta-input" required placeholder="e.g. STORE">
                    </div>
                    <div class="ta-field">
                        <label>Type *</label>
                        <select name="type" class="ta-select" required>
                            @foreach(\App\Models\StockLocation::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ta-field">
                        <label>Sort order</label>
                        <input type="number" name="sort_order" class="ta-input" value="10" min="0">
                    </div>
                    <div class="ta-field sm:col-span-2">
                        <label>Description</label>
                        <input type="text" name="description" class="ta-input" placeholder="Optional notes">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ta-check">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Active location</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="ta-btn-outline" @click="addOpen = false">Cancel</button>
                    <button type="submit" class="ta-btn">Save location</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5 pb-20 lg:pb-0" x-data="locationEditor()">
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-blue !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600">↑ 0%</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Locations</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-success !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700">↑ {{ $stats['active_pct'] ?? 0 }}%</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Active Locations</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-purple !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700">Units</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Stock</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['units']) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-warning !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <span class="rounded-full bg-warning-50 px-2 py-0.5 text-[10px] font-bold text-warning-700">SKUs</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Stocked SKUs</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['sku_rows']) }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4" x-data="{ filtersOpen: {{ $filtersOpen ? 'true' : 'false' }} }">
        <div class="mb-3 flex items-center justify-between lg:hidden">
            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Filters</p>
            <button type="button" @click="filtersOpen = !filtersOpen" class="ta-btn-outline ta-btn-sm">
                <span x-text="filtersOpen ? 'Hide' : 'Show'"></span>
            </button>
        </div>
        <form method="GET" action="{{ route('admin.stock-locations.index') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4"
              :class="filtersOpen ? 'grid' : 'hidden lg:grid'">
            <div class="ta-field sm:col-span-2">
                <label>Search</label>
                <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Search location name or code…">
            </div>
            <div class="ta-field">
                <label>Location type</label>
                <select name="type" class="ta-select">
                    <option value="">All types</option>
                    @foreach(\App\Models\StockLocation::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Status</label>
                <select name="status" class="ta-select">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-4">
                <button type="submit" class="ta-btn">Filter</button>
                @if($filtersOpen)
                    <a href="{{ route('admin.stock-locations.index') }}" class="ta-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
            <div>
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90 sm:text-base">Locations</h2>
                <p class="text-xs text-gray-500 sm:text-sm">{{ $locations->count() }} location{{ $locations->count() === 1 ? '' : 's' }}</p>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 p-3 lg:hidden">
            @forelse($locations as $location)
                @php
                    $typeLabel = \App\Models\StockLocation::TYPES[$location->type] ?? $location->type;
                    $iconPath = $typeIcons[$location->type] ?? $typeIcons['other'];
                    $units = (int) ($location->stock_units ?? 0);
                    $skus = (int) ($location->sku_rows ?? 0);
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-800 dark:text-white/90">{{ $location->name }}</p>
                                    <p class="truncate text-xs text-gray-400">{{ $location->description ?: $typeLabel }}</p>
                                </div>
                                <span class="ta-pill {{ $location->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <p class="text-gray-400">Code</p>
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ $location->code }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400">Units</p>
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($units) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400">SKUs</p>
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($skus) }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-end gap-1 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <a href="{{ route('admin.stock-overview.index', ['location_id' => $location->id]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600" title="View stock">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600" title="Edit"
                                    @click="openEdit(@js([
                                        'id' => $location->id,
                                        'name' => $location->name,
                                        'code' => $location->code,
                                        'type' => $location->type,
                                        'sort_order' => $location->sort_order,
                                        'description' => $location->description,
                                        'is_active' => $location->is_active,
                                        'action' => route('admin.stock-locations.update', $location),
                                    ]))">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-10 text-center text-sm text-gray-400 dark:border-gray-800">No locations found.</div>
            @endforelse
        </div>

        <div class="ta-table-wrap hidden lg:block">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Location</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th class="text-right">Units</th>
                        <th class="text-right">SKUs</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($locations as $index => $location)
                    @php
                        $typeLabel = \App\Models\StockLocation::TYPES[$location->type] ?? $location->type;
                        $iconPath = $typeIcons[$location->type] ?? $typeIcons['other'];
                        $units = (int) ($location->stock_units ?? 0);
                        $skus = (int) ($location->sku_rows ?? 0);
                    @endphp
                    <tr>
                        <td class="text-gray-400">{{ $index + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="ta-name">{{ $location->name }}</div>
                                    <div class="ta-muted truncate">{{ $location->description ?: 'No description' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="ta-pill ta-pill-muted">{{ $location->code }}</span></td>
                        <td>{{ $typeLabel }}</td>
                        <td class="text-right font-semibold text-gray-800 dark:text-white/90">{{ number_format($units) }}</td>
                        <td class="text-right text-gray-500">{{ number_format($skus) }}</td>
                        <td>{{ $location->sort_order }}</td>
                        <td>
                            <span class="ta-pill {{ $location->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.stock-overview.index', ['location_id' => $location->id]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="View stock">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="Edit"
                                    @click="openEdit(@js([
                                        'id' => $location->id,
                                        'name' => $location->name,
                                        'code' => $location->code,
                                        'type' => $location->type,
                                        'sort_order' => $location->sort_order,
                                        'description' => $location->description,
                                        'is_active' => $location->is_active,
                                        'action' => route('admin.stock-locations.update', $location),
                                    ]))">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.stock-locations.destroy', $location) }}" onsubmit="return confirm('{{ $location->canBeDeleted() ? 'Delete this location?' : 'This location has history and will be deactivated. Continue?' }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-error-500 hover:bg-error-50" title="{{ $location->canBeDeleted() ? 'Delete' : 'Deactivate' }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="ta-empty">No locations yet. Add Main Store and Shop to get started.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-4 py-3 text-sm text-gray-500 dark:border-gray-800 sm:px-5">
            Showing {{ $locations->count() }} of {{ $stats['total'] }} locations
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-gray-900/50" @click="open = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900" @click.stop>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Edit location</h3>
            <form method="POST" :action="form.action" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="ta-field sm:col-span-2">
                        <label>Name *</label>
                        <input type="text" name="name" class="ta-input" x-model="form.name" required>
                    </div>
                    <div class="ta-field">
                        <label>Code *</label>
                        <input type="text" name="code" class="ta-input" x-model="form.code" required>
                    </div>
                    <div class="ta-field">
                        <label>Type *</label>
                        <select name="type" class="ta-select" x-model="form.type" required>
                            @foreach(\App\Models\StockLocation::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ta-field">
                        <label>Sort order</label>
                        <input type="number" name="sort_order" class="ta-input" x-model="form.sort_order" min="0">
                    </div>
                    <div class="ta-field sm:col-span-2">
                        <label>Description</label>
                        <input type="text" name="description" class="ta-input" x-model="form.description">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ta-check">
                            <input type="checkbox" name="is_active" value="1" x-model="form.is_active">
                            <span>Active location</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="ta-btn-outline" @click="open = false">Cancel</button>
                    <button type="submit" class="ta-btn">Update location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function locationEditor() {
    return {
        open: false,
        form: { id: null, name: '', code: '', type: 'store', sort_order: 0, description: '', is_active: true, action: '' },
        openEdit(data) {
            this.form = { ...data, is_active: !!data.is_active };
            this.open = true;
        }
    };
}
</script>
@endsection
