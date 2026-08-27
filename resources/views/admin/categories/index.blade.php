@extends('layouts.admin')
@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'Organize products for storefront browsing.')

@push('styles')
<style>
.cat-page { display:flex; flex-direction:column; gap:18px; }
.cat-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
@media(max-width:600px){ .cat-kpis { grid-template-columns:1fr; } }
.cat-kpi { background:#fff; border:1px solid #eaecf0; border-radius:16px; padding:18px 20px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.cat-kpi-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.cat-kpi-icon svg { width:22px; height:22px; }
.cat-kpi-icon.purple { background:#f3f0ff; color:#7c3aed; }
.cat-kpi-icon.green  { background:#ecfdf5; color:#059669; }
.cat-kpi-icon.orange { background:#fff7ed; color:#ea580c; }
.cat-kpi-label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
.cat-kpi-value { font-size:1.5rem; font-weight:800; color:#111827; margin-top:2px; }
.cat-toolbar { background:#fff; border:1px solid #eaecf0; border-radius:16px; padding:14px 18px; display:flex; justify-content:flex-end; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.cat-btn-add { background:linear-gradient(135deg,#d4af37,#b8942d); color:#1a1300; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:0 3px 10px rgba(212,175,55,.25); }
.cat-btn-add:hover { opacity:.9; }
.cat-card { background:#fff; border:1px solid #eaecf0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.cat-table { width:100%; border-collapse:collapse; }
.cat-table thead tr { background:#f9fafb; border-bottom:1px solid #eaecf0; }
.cat-table thead th { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; padding:11px 16px; text-align:left; }
.cat-table tbody tr { border-bottom:1px solid #f3f4f6; transition:background .12s; }
.cat-table tbody tr:last-child { border-bottom:none; }
.cat-table tbody tr:hover { background:rgba(212,175,55,.04); }
.cat-table td { padding:14px 16px; font-size:13px; color:#374151; vertical-align:middle; }
.cat-name { font-weight:700; color:#111827; }
.cat-slug { font-size:11px; color:#9ca3af; font-family:monospace; }
.cat-pill { display:inline-flex; border-radius:999px; padding:3px 10px; font-size:11.5px; font-weight:700; }
.cat-pill.on  { background:#dcfce7; color:#166534; }
.cat-pill.off { background:#f3f4f6; color:#4b5563; }
.iact3 { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer; text-decoration:none; }
.iact3:hover { background:#f3f4f6; }
.iact3.edit:hover { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.iact3.del { border-color:#fee2e2; color:#ef4444; }
.iact3.del:hover { background:#fee2e2; }
.iact3 svg { width:15px; height:15px; }
.iact3-row { display:flex; gap:6px; align-items:center; justify-content:flex-end; }
.cat-footer { padding:12px 20px; border-top:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
.cat-showing { font-size:12px; color:#6b7280; }
</style>
@endpush

@section('content')
<div class="cat-page">

    <div class="cat-kpis">
        <div class="cat-kpi">
            <div class="cat-kpi-icon purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div><div class="cat-kpi-label">Total Categories</div><div class="cat-kpi-value">{{ $stats['total'] }}</div></div>
        </div>
        <div class="cat-kpi">
            <div class="cat-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="cat-kpi-label">Active</div><div class="cat-kpi-value">{{ $stats['active'] }}</div></div>
        </div>
        <div class="cat-kpi">
            <div class="cat-kpi-icon orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div><div class="cat-kpi-label">With Products</div><div class="cat-kpi-value">{{ $categories->where('products_count', '>', 0)->count() }}</div></div>
        </div>
    </div>

    <div class="cat-toolbar">
        <a href="{{ route('admin.categories.create') }}" class="cat-btn-add">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </a>
    </div>

    <div class="cat-card">
        <div style="overflow-x:auto;">
            <table class="cat-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th style="text-align:right;padding-right:18px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><span class="cat-name">{{ $category->name }}</span></td>
                        <td><span class="cat-slug">{{ $category->slug }}</span></td>
                        <td>
                            <span style="font-weight:700;color:#111827;">{{ $category->products_count }}</span>
                            <span style="font-size:11px;color:#9ca3af;"> products</span>
                        </td>
                        <td>
                            <span class="cat-pill {{ $category->is_active ? 'on' : 'off' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding-right:18px;">
                            <div class="iact3-row">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="iact3 edit" title="Edit">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="iact3 del" title="Delete" onclick="return confirm('Delete {{ addslashes($category->name) }}?')">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:48px;color:#9ca3af;font-size:14px;">No categories yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="cat-footer">
            <span class="cat-showing">{{ $categories->total() }} total categories</span>
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
