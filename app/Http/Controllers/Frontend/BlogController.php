<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::where('published', true)->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $posts = $query->paginate(10)->withQueryString();

        $categories = BlogPost::where('published', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('frontend.blog.index', compact('posts', 'categories'));
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('published', true)
            ->with(['comments' => fn ($query) => $query->where('approved', true)])
            ->firstOrFail();

        return view('frontend.blog.show', compact('post'));
    }

    public function comment(Request $request, $slug)
    {
        $post = BlogPost::where('slug', $slug)->where('published', true)->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|max:2000',
        ]);

        $data['approved'] = true;

        $post->comments()->create($data);

        return redirect()
            ->route('blog.show', $post->slug)
            ->with('success', 'Your comment has been posted.');
    }
}
