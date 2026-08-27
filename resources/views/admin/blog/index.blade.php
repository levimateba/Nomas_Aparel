@extends('layouts.admin')
@section('title', 'Blog')
@section('heading', 'Blog')
@section('subheading', 'Publish stories and updates for your store.')

@push('styles')
<style>
.blg-page { display:flex; flex-direction:column; gap:18px; }
.blg-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
@media(max-width:600px){ .blg-kpis { grid-template-columns:1fr; } }
.blg-kpi { background:#fff; border:1px solid #eaecf0; border-radius:16px; padding:18px 20px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.blg-kpi-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.blg-kpi-icon svg { width:22px; height:22px; }
.blg-kpi-icon.gold   { background:#fffbeb; color:#d97706; }
.blg-kpi-icon.green  { background:#ecfdf5; color:#059669; }
.blg-kpi-icon.blue   { background:#eff6ff; color:#3b82f6; }
.blg-kpi-label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
.blg-kpi-value { font-size:1.5rem; font-weight:800; color:#111827; margin-top:2px; }

.blg-toolbar { background:#fff; border:1px solid #eaecf0; border-radius:16px; padding:14px 18px; display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.blg-field { display:flex; flex-direction:column; gap:5px; }
.blg-field label { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; }
.blg-field select { padding:9px 12px; border:1px solid #d1d5db; border-radius:10px; font-size:13px; margin:0 !important; min-width:160px; outline:none; }
.blg-field select:focus { border-color:#d4af37; box-shadow:0 0 0 3px rgba(212,175,55,.12); }
.blg-btn-apply { background:linear-gradient(135deg,#d4af37,#b8942d); color:#1a1300; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.blg-btn-apply:hover { opacity:.92; }
.blg-btn-outline { background:#fff; color:#374151; border:1px solid #d1d5db; border-radius:10px; padding:9px 14px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.blg-btn-outline:hover { background:#f9fafb; }
.blg-btn-add { background:linear-gradient(135deg,#d4af37,#b8942d); color:#1a1300; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:0 3px 10px rgba(212,175,55,.25); margin-left:auto; }
.blg-btn-add:hover { opacity:.9; }

.blg-card { background:#fff; border:1px solid #eaecf0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.blg-table { width:100%; border-collapse:collapse; }
.blg-table thead tr { background:#f9fafb; border-bottom:1px solid #eaecf0; }
.blg-table thead th { font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; padding:11px 16px; text-align:left; }
.blg-table tbody tr { border-bottom:1px solid #f3f4f6; transition:background .12s; }
.blg-table tbody tr:last-child { border-bottom:none; }
.blg-table tbody tr:hover { background:rgba(212,175,55,.04); }
.blg-table td { padding:14px 16px; font-size:13px; color:#374151; vertical-align:middle; }
.blg-title { font-weight:700; color:#111827; font-size:13.5px; }
.blg-excerpt { font-size:11px; color:#9ca3af; margin-top:2px; max-width:340px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.blg-pill { display:inline-flex; border-radius:999px; padding:3px 10px; font-size:11.5px; font-weight:700; }
.blg-pill.published { background:#dcfce7; color:#166534; }
.blg-pill.draft     { background:#f3f4f6; color:#4b5563; }
.iact2 { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer; text-decoration:none; transition:background .12s; }
.iact2:hover { background:#f3f4f6; }
.iact2.edit:hover { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.iact2.del { border-color:#fee2e2; color:#ef4444; }
.iact2.del:hover { background:#fee2e2; }
.iact2 svg { width:15px; height:15px; }
.iact2-row { display:flex; gap:6px; align-items:center; justify-content:flex-end; }
.blg-footer { padding:12px 20px; border-top:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
.blg-showing { font-size:12px; color:#6b7280; }
</style>
@endpush

@section('content')
<div class="blg-page">

    <div class="blg-kpis">
        <div class="blg-kpi">
            <div class="blg-kpi-icon gold">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div><div class="blg-kpi-label">Total Posts</div><div class="blg-kpi-value">{{ $stats['total'] }}</div></div>
        </div>
        <div class="blg-kpi">
            <div class="blg-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="blg-kpi-label">Published</div><div class="blg-kpi-value">{{ $stats['published'] }}</div></div>
        </div>
        <div class="blg-kpi">
            <div class="blg-kpi-icon blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
            </div>
            <div><div class="blg-kpi-label">Drafts</div><div class="blg-kpi-value">{{ ($stats['total'] ?? 0) - ($stats['published'] ?? 0) }}</div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.blog.index') }}">
        <div class="blg-toolbar">
            <div class="blg-field">
                <label>Category</label>
                <select name="category">
                    <option value="">All categories</option>
                    @foreach($blogCategories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="blg-btn-apply">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Apply
                </button>
                @if(request('category'))
                    <a href="{{ route('admin.blog.index') }}" class="blg-btn-outline">Clear</a>
                @endif
            </div>
            <a href="{{ route('admin.blog.create') }}" class="blg-btn-add">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Post
            </a>
        </div>
    </form>

    <div class="blg-card">
        <div style="overflow-x:auto;">
            <table class="blg-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align:right;padding-right:18px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <div class="blg-title">{{ $post->title }}</div>
                            @if($post->excerpt ?? false)
                                <div class="blg-excerpt">{{ $post->excerpt }}</div>
                            @endif
                        </td>
                        <td>{{ $post->category ?: 'Uncategorized' }}</td>
                        <td>{{ $post->author ?: 'Admin' }}</td>
                        <td>
                            <span class="blg-pill {{ $post->published_at ? 'published' : 'draft' }}">
                                {{ $post->published_at ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td style="color:#9ca3af;font-size:12px;">{{ $post->created_at->format('d M Y') }}</td>
                        <td style="padding-right:18px;">
                            <div class="iact2-row">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="iact2" title="View">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.blog.edit', $post) }}" class="iact2 edit" title="Edit">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="iact2 del" title="Delete" onclick="return confirm('Delete this post?')">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:48px;color:#9ca3af;font-size:14px;">No posts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="blg-footer">
            <span class="blg-showing">{{ $posts->total() }} total posts</span>
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
