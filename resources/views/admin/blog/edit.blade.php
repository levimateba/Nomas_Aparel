@extends('layouts.admin')
@section('title', 'Edit Blog Post')
@section('heading', 'Edit Blog Post')
@section('subheading', 'Dashboard › Blog › ' . Str::limit($post->title, 50))

@push('styles')
<style>
.bp-page { display:grid; grid-template-columns:minmax(0,1.7fr) 300px; gap:20px; align-items:start; }
@media(max-width:960px){ .bp-page { grid-template-columns:1fr; } }
.bp-card { background:#fff; border:1px solid #eaecf0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.bp-card-head { padding:16px 20px; border-bottom:1px solid #eaecf0; background:#fafafa; }
.bp-card-title { font-size:15px; font-weight:700; color:#111827; margin:0; }
.bp-card-sub   { font-size:12px; color:#6b7280; margin-top:4px; }
.bp-card-body  { padding:20px; }
.bp-preview-panel { position:sticky; top:88px; }
@media(max-width:960px){ .bp-preview-panel { position:static; } }
.bp-preview { background:#fff; border:1px solid #eaecf0; border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.bp-preview-head { padding:14px 18px; border-bottom:1px solid #eaecf0; background:#fafafa; font-size:14px; font-weight:700; color:#111827; }
.bp-preview-body { padding:16px; display:grid; gap:10px; }
.bp-img-box { border-radius:12px; border:1px dashed #d1d5db; background:#f9fafb; min-height:150px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
.bp-img-box img { width:100%; height:150px; object-fit:cover; border-radius:12px; }
.bp-img-placeholder { text-align:center; color:#9ca3af; font-size:12px; }
.bp-badge { border-radius:999px; padding:4px 12px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; display:inline-flex; }
.bp-badge.live  { background:#dcfce7; color:#166534; }
.bp-badge.draft { background:#f3f4f6; color:#4b5563; }
.bp-prev-title  { font-size:15px; font-weight:800; color:#111827; margin:0; }
.bp-prev-meta   { font-size:12px; color:#9ca3af; margin:0; }
.bp-prev-excerpt { font-size:13px; color:#374151; margin:0; line-height:1.5; }
.bp-chips { display:flex; flex-wrap:wrap; gap:6px; }
.bp-chip { background:#f3f4f6; border:1px solid #e5e7eb; border-radius:999px; padding:3px 10px; font-size:11px; color:#374151; }
.bp-bottom { background:#fff; border:1px solid #eaecf0; border-radius:16px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; box-shadow:0 1px 4px rgba(0,0,0,.05); margin-top:16px; }
.bp-btn-group  { display:flex; gap:10px; }
.bp-btn-cancel  { background:#fff; border:1px solid #d1d5db; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:700; color:#374151; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.bp-btn-cancel:hover { background:#f9fafb; }
.bp-btn-danger  { background:#fff; border:1px solid #fee2e2; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:700; color:#dc2626; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.bp-btn-danger:hover { background:#fee2e2; }
.bp-btn-primary { background:linear-gradient(135deg,#d4af37,#b8942d); border:none; border-radius:10px; padding:10px 22px; font-size:14px; font-weight:800; color:#1a1300; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(212,175,55,.3); }
.bp-btn-primary:hover { opacity:.92; }

/* Form field styling */
.blog-input {
    padding:10px 12px; border:1px solid #d1d5db; border-radius:12px; font-size:14px;
    color:#111827; background:#fff; width:100%; outline:none;
    transition:border-color .15s,box-shadow .15s; margin:0 !important;
}
textarea.blog-input { resize:vertical; min-height:100px; }
.blog-input:focus { border-color:#d4af37; box-shadow:0 0 0 3px rgba(212,175,55,.12); background:#fff; }
.blog-input.is-invalid { border-color:#ef4444; }
.blog-field { display:grid; gap:6px; }
.blog-field label { font-size:13px; font-weight:700; color:#374151; }
.blog-help  { font-size:11.5px; color:#9ca3af; }
.blog-error { font-size:12px; color:#dc2626; font-weight:600; }
.blog-row-two { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:700px){ .blog-row-two { grid-template-columns:1fr; } }
.blog-switch { display:flex; align-items:center; gap:8px; padding:10px 12px; border:1px solid #d1d5db; border-radius:12px; background:#fafafa; }
.blog-switch input { width:16px; height:16px; accent-color:#d4af37; cursor:pointer; }
.blog-switch span { font-size:13px; font-weight:600; color:#374151; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="bp-page">
    {{-- Form --}}
    <div class="bp-card">
        <div class="bp-card-head">
            <div class="bp-card-title">Post Details</div>
            <div class="bp-card-sub">Update metadata, body content, image, and publishing settings.</div>
        </div>
        <div class="bp-card-body">
            @include('admin.blog._form', ['post' => $post])
        </div>
    </div>

    {{-- Preview --}}
    <div class="bp-preview-panel">
        <div class="bp-preview">
            <div class="bp-preview-head">Live Preview</div>
            <div class="bp-preview-body">
                <span id="blog-publish-badge" class="bp-badge {{ ($post->published_at ?? null) ? 'live' : 'draft' }}">{{ ($post->published_at ?? null) ? 'Published' : 'Draft' }}</span>
                <div class="bp-img-box" id="blog-image-preview">
                    @if(!empty($post->image))
                        <img src="{{ $post->image }}" alt="Current post image" style="width:100%;height:150px;object-fit:cover;border-radius:12px;">
                    @else
                        <div class="bp-img-placeholder">
                            <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                            <div style="margin-top:6px;">No image</div>
                        </div>
                    @endif
                </div>
                <h4 id="blog-title-preview" class="bp-prev-title">{{ old('title', $post->title ?: 'Post title preview') }}</h4>
                <p id="blog-meta-preview" class="bp-prev-meta">By {{ old('author', $post->author ?: 'Author') }} | {{ old('category', $post->category ?: 'Uncategorized') }}</p>
                <p id="blog-excerpt-preview" class="bp-prev-excerpt">{{ old('excerpt', $post->excerpt ?: 'Your short summary will appear here.') }}</p>
                <div class="bp-chips">
                    <span id="blog-content-count" class="bp-chip">Content: {{ strlen(old('content', $post->content ?? '')) }} chars</span>
                    <span id="blog-excerpt-count" class="bp-chip">Excerpt: {{ strlen(old('excerpt', $post->excerpt ?? '')) }} chars</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bp-bottom">
    <a href="{{ route('admin.blog.index') }}" class="bp-btn-cancel">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Cancel
    </a>
    <div class="bp-btn-group">
        <button type="button" class="bp-btn-danger" onclick="document.getElementById('bp-delete-form').submit()" onmousedown="return confirm('Delete this post permanently?')">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete Post
        </button>
        <button type="submit" class="bp-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Update Post
        </button>
    </div>
</div>
</form>

<form id="bp-delete-form" method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">
    @csrf @method('DELETE')
</form>

<script>
(function(){
    const titleInput    = document.getElementById('blog-title');
    const categoryInput = document.getElementById('blog-category');
    const authorInput   = document.getElementById('blog-author');
    const excerptInput  = document.getElementById('blog-excerpt');
    const contentInput  = document.getElementById('blog-content');
    const publishInput  = document.getElementById('blog-published');
    const imageInput    = document.getElementById('blog-image');
    const titlePreview  = document.getElementById('blog-title-preview');
    const metaPreview   = document.getElementById('blog-meta-preview');
    const excerptPreview= document.getElementById('blog-excerpt-preview');
    const publishBadge  = document.getElementById('blog-publish-badge');
    const contentCount  = document.getElementById('blog-content-count');
    const excerptCount  = document.getElementById('blog-excerpt-count');
    const imgPreview    = document.getElementById('blog-image-preview');

    function selText(el,fb){ if(!el)return fb; const o=el.options[el.selectedIndex]; return o&&o.text?o.text:fb; }
    function setImg(src){
        if(!imgPreview)return; imgPreview.innerHTML='';
        if(src){ const i=document.createElement('img'); i.src=src; i.style.cssText='width:100%;height:150px;object-fit:cover;border-radius:12px;'; imgPreview.appendChild(i); }
        else { imgPreview.innerHTML='<div class="bp-img-placeholder"><svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/></svg><div style="margin-top:6px;">No image</div></div>'; }
    }
    function update(){
        const title=((titleInput?.value)||'').trim();
        const cat=selText(categoryInput,'Uncategorized');
        const author=((authorInput?.value)||'').trim();
        const excerpt=((excerptInput?.value)||'').trim();
        const content=((contentInput?.value)||'').trim();
        const pub=!!(publishInput?.checked);
        if(titlePreview) titlePreview.textContent=title||'Post title preview';
        if(metaPreview) metaPreview.textContent='By '+(author||'Author')+' | '+cat;
        if(excerptPreview) excerptPreview.textContent=excerpt||'Your short summary will appear here.';
        if(contentCount) contentCount.textContent='Content: '+content.length+' chars';
        if(excerptCount) excerptCount.textContent='Excerpt: '+excerpt.length+' chars';
        if(publishBadge){ publishBadge.className='bp-badge '+(pub?'live':'draft'); publishBadge.textContent=pub?'Published':'Draft'; }
    }
    [titleInput,categoryInput,authorInput,excerptInput,contentInput,publishInput].forEach(el=>{ if(!el)return; el.addEventListener('input',update); el.addEventListener('change',update); });
    imageInput?.addEventListener('change',()=>{ if(imageInput.files?.[0]){ const r=new FileReader(); r.onload=e=>setImg(e.target.result); r.readAsDataURL(imageInput.files[0]); } else { setImg(null); } });
    update();
})();
</script>
@endsection
