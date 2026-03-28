<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('published', true)->latest('published_at')->paginate(10);
        return view('frontend.blog.index', compact('posts'));
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
