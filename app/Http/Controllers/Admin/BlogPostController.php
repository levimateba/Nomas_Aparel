<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index()
    {
        $query = BlogPost::latest('published_at');

        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }

        $posts = $query->paginate(10)->withQueryString();
        $blogCategories = config('content_taxonomy.blog_categories', []);
        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::whereNotNull('published_at')->count(),
        ];

        return view('admin.blog.index', compact('posts', 'blogCategories', 'stats'));
    }

    public function create()
    {
        $blogCategories = config('content_taxonomy.blog_categories', []);

        return view('admin.blog.create', compact('blogCategories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => ['nullable', 'string', Rule::in(config('content_taxonomy.blog_categories', []))],
            'author' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'published' => 'nullable|boolean',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['published'] = $request->boolean('published', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blog', 'public');
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.blog.index');
    }

    public function edit(BlogPost $blog)
    {
        $post = $blog;
        $blogCategories = config('content_taxonomy.blog_categories', []);

        return view('admin.blog.edit', compact('post', 'blogCategories'));
    }

    public function update(Request $request, BlogPost $blog)
    {
        $post = $blog;

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => ['nullable', 'string', Rule::in(config('content_taxonomy.blog_categories', []))],
            'author' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'published' => 'nullable|boolean',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['published'] = $request->boolean('published', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blog', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Blog post removed.');
    }
}
