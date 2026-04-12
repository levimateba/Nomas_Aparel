@extends('layouts.storefront')

@section('title',$post->title)

@section('content')
    <section class="py-5">
        <div class="container">
            <article class="card p-4 shadow-sm border-0" style="border-radius: 16px;">
                @if($post->image)
                    <img src="{{ $post->image }}" alt="{{ $post->title }}" style="width:100%;max-height:420px;object-fit:cover;border-radius:14px;margin-bottom:24px;">
                @endif
                <div class="mb-3">
                    @if($post->category)
                        <span class="badge badge-pill badge-success px-3 py-2">{{ $post->category }}</span>
                    @endif
                    <span class="text-muted ml-2">{{ ($post->published_at ?? $post->created_at)->format('M d, Y') }}</span>
                    @if($post->author)
                        <span class="text-muted ml-2">By {{ $post->author }}</span>
                    @endif
                </div>
                <h1 class="mb-3" style="color: var(--primary-dark);">{{ $post->title }}</h1>
                @if($post->excerpt)
                    <p class="lead text-muted">{{ $post->excerpt }}</p>
                @endif
                <div style="line-height:1.9;color:#48535d;">{!! nl2br(e($post->content)) !!}</div>

                @php
                    $postUrl = urlencode(route('blog.show', $post->slug));
                    $shareText = urlencode($post->title);
                @endphp

                <div class="mt-5 pt-4 border-top">
                    <h4 style="color: var(--primary-dark);">Share this post</h4>
                    <div class="d-flex flex-wrap">
                        <a class="btn btn-outline-primary mr-2 mb-2" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $postUrl }}">Facebook</a>
                        <a class="btn btn-outline-info mr-2 mb-2" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $postUrl }}&text={{ $shareText }}">Twitter</a>
                        <a class="btn btn-outline-success mb-2" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($post->title . ' ' . route('blog.show', $post->slug)) }}">WhatsApp</a>
                    </div>
                </div>
            </article>

            <div class="card p-4 shadow-sm border-0 mt-4" style="border-radius: 16px;">
                <h3 style="color: var(--primary-dark);">Comments</h3>

                @if(session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('blog.comment', $post->slug) }}" class="mt-3">
                    @csrf
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Your name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Your email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <textarea name="comment" rows="4" class="form-control" placeholder="Write your comment" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>

                <div class="mt-4">
                    @forelse($post->comments as $comment)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ $comment->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->format('M d, Y H:i') }}</small>
                            </div>
                            <p class="mb-0">{{ $comment->comment }}</p>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No comments yet. Be the first to comment.</p>
                    @endforelse
                </div>
            </div>

            <a class="btn btn-primary mt-4" href="{{ route('blog.index') }}">Back to blog</a>
        </div>
    </section>
@endsection
