@extends('layouts.frontend')

@section('title','Blog')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Blog</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">Insights, updates, and stories from our team.</p>
                <div class="line mx-auto"></div>
            </div>

            <div class="row">
                @forelse($posts as $post)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                            @if($post->image)
                                <img src="{{ $post->image }}" alt="{{ $post->title }}" style="width:100%;height:220px;object-fit:cover;">
                            @endif
                            <div class="p-4">
                                @if($post->category)
                                    <span class="badge badge-pill badge-success px-3 py-2 mb-3">{{ $post->category }}</span>
                                @endif
                                <h3 style="font-size: 1.2rem;"><a href="{{ route('blog.show', $post->slug) }}" style="color: var(--primary-dark); text-decoration:none;">{{ $post->title }}</a></h3>
                                <p class="text-muted">{{ $post->excerpt ?: \Illuminate\Support\Str::limit($post->content, 120) }}</p>
                                <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p>No posts yet.</p>
                    </div>
                @endforelse
            </div>

            {{ $posts->links() }}
        </div>
    </section>
@endsection
