@extends('layouts.admin')
@section('title', 'Create Category')
@section('heading', 'Create Category')
@section('subheading', 'Dashboard › Categories › Create')

@push('styles')
<style>
.cf-page { display:grid; grid-template-columns:minmax(0,1.5fr) 280px; gap:20px; align-items:start; }
@media(max-width:860px){ .cf-page { grid-template-columns:1fr; } }
.cf-card { background:#fff; border:1px solid #eaecf0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); margin-bottom:16px; }
.cf-card-head { display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid #eaecf0; background:#fafafa; }
.cf-card-title { font-size:15px; font-weight:700; color:#111827; margin:0; }
.cf-card-sub   { font-size:12px; color:#6b7280; margin-top:2px; }
.cf-card-body  { padding:22px; display:grid; gap:16px; }
.cf-field { display:flex; flex-direction:column; gap:6px; }
.cf-label { font-size:13px; font-weight:700; color:#374151; display:flex; justify-content:space-between; }
.cf-req { font-size:11px; color:#ef4444; font-weight:600; }
.cf-input { padding:10px 12px; border:1px solid #d1d5db; border-radius:12px; font-size:14px; color:#111827; background:#fff; width:100%; outline:none; transition:border-color .15s,box-shadow .15s; margin:0 !important; }
.cf-input:focus { border-color:#d4af37; box-shadow:0 0 0 3px rgba(212,175,55,.12); }
.cf-input.is-invalid { border-color:#ef4444; }
.cf-help  { font-size:11.5px; color:#9ca3af; }
.cf-error { font-size:12px; color:#dc2626; font-weight:600; }
.cf-toggle { display:flex; align-items:center; gap:8px; }
.cf-toggle input { width:16px; height:16px; accent-color:#d4af37; cursor:pointer; }
.cf-toggle label { font-size:13px; font-weight:600; color:#374151; cursor:pointer; margin:0; }

/* Preview panel */
.cf-panel { background:#fff; border:1px solid #eaecf0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); position:sticky; top:88px; }
@media(max-width:860px){ .cf-panel { position:static; } }
.cf-panel-head { padding:14px 18px; border-bottom:1px solid #eaecf0; background:#fafafa; font-size:14px; font-weight:700; color:#111827; }
.cf-preview { padding:20px; text-align:center; display:grid; gap:12px; }
.cf-preview-icon { width:64px; height:64px; border-radius:16px; background:#f3f0ff; color:#7c3aed; display:flex; align-items:center; justify-content:center; margin:0 auto; }
.cf-badge { border-radius:999px; padding:4px 12px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; display:inline-flex; }
.cf-badge.active   { background:#dcfce7; color:#166534; }
.cf-badge.inactive { background:#f3f4f6; color:#4b5563; }
.cf-preview-name { font-size:16px; font-weight:800; color:#111827; }
.cf-preview-slug { font-size:12px; color:#9ca3af; font-family:monospace; word-break:break-all; }

/* Bottom bar */
.cf-bottom { background:#fff; border:1px solid #eaecf0; border-radius:16px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.cf-btn-cancel  { background:#fff; border:1px solid #d1d5db; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:700; color:#374151; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.cf-btn-cancel:hover { background:#f9fafb; }
.cf-btn-primary { background:linear-gradient(135deg,#d4af37,#b8942d); border:none; border-radius:10px; padding:10px 22px; font-size:14px; font-weight:800; color:#1a1300; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(212,175,55,.3); }
.cf-btn-primary:hover { opacity:.92; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.categories.store') }}">
@csrf
<div class="cf-page">
    <div>
        <div class="cf-card">
            <div class="cf-card-head">
                <div>
                    <div class="cf-card-title">Category Details</div>
                    <div class="cf-card-sub">Add a product category with a clean name and URL slug.</div>
                </div>
            </div>
            <div class="cf-card-body">
                <div class="cf-field">
                    <label class="cf-label" for="category-name">
                        Name
                        <span class="cf-req">Required</span>
                    </label>
                    <input id="category-name" type="text" name="name" class="cf-input @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="255" placeholder="e.g. Suits, Bags, Uniforms…" required>
                    <span class="cf-help">Use a short, recognizable label your customers understand.</span>
                    @error('name')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="category-slug">Slug (URL)</label>
                    <input id="category-slug" type="text" name="slug" class="cf-input @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="auto-generated-if-empty">
                    <span class="cf-help">Leave blank to auto-generate from name. Used in the URL: /category/<strong>slug</strong></span>
                    @error('slug')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <div class="cf-toggle">
                        <input id="category-active" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="category-active">Active category</label>
                    </div>
                    <span class="cf-help">Inactive categories are hidden from storefront browsing.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="cf-panel">
        <div class="cf-panel-head">Preview</div>
        <div class="cf-preview">
            <div class="cf-preview-icon">
                <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <span id="category-status" class="cf-badge active">Active</span>
            <div id="category-name-preview" class="cf-preview-name">Category Name</div>
            <div id="category-slug-preview" class="cf-preview-slug">/category/slug-preview</div>
        </div>
    </div>
</div>

<div class="cf-bottom">
    <a href="{{ route('admin.categories.index') }}" class="cf-btn-cancel">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Cancel
    </a>
    <button type="submit" class="cf-btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        Create Category
    </button>
</div>
</form>

<script>
(function(){
    const nameInput = document.getElementById('category-name');
    const slugInput = document.getElementById('category-slug');
    const activeInput = document.getElementById('category-active');
    const namePreview = document.getElementById('category-name-preview');
    const slugPreview = document.getElementById('category-slug-preview');
    const statusPreview = document.getElementById('category-status');
    function toSlug(v){ return v.toLowerCase().trim().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-'); }
    function update(){
        const name = (nameInput.value||'').trim();
        const slug = (slugInput.value||'').trim();
        const finalSlug = slug || toSlug(name);
        const active = !!activeInput.checked;
        namePreview.textContent = name || 'Category Name';
        slugPreview.textContent = finalSlug ? '/category/'+finalSlug : '/category/slug-preview';
        statusPreview.className = 'cf-badge '+(active?'active':'inactive');
        statusPreview.textContent = active ? 'Active' : 'Inactive';
    }
    [nameInput,slugInput,activeInput].forEach(el => { el?.addEventListener('input',update); el?.addEventListener('change',update); });
    update();
})();
</script>
@endsection
