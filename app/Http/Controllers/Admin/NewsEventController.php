<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsEvent;
use Illuminate\Http\Request;

class NewsEventController extends Controller
{
    public function index()
    {
        $items = NewsEvent::latest('event_date')->paginate(10);
        return view('admin.news-event.index', compact('items'));
    }

    public function create()
    {
        return view('admin.news-event.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'event_date' => 'nullable|date',
            'published' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data['published'] = $request->boolean('published', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news-events', 'public');
        }

        NewsEvent::create($data);

        return redirect()->route('admin.news-events.index')->with('success', 'News or event created.');
    }

    public function edit(NewsEvent $news_event)
    {
        return view('admin.news-event.edit', ['item' => $news_event]);
    }

    public function update(Request $request, NewsEvent $news_event)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'event_date' => 'nullable|date',
            'published' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data['published'] = $request->boolean('published', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news-events', 'public');
        }

        $news_event->update($data);

        return redirect()->route('admin.news-events.index')->with('success', 'News or event updated.');
    }

    public function destroy(NewsEvent $news_event)
    {
        $news_event->delete();
        return redirect()->route('admin.news-events.index')->with('success', 'News or event deleted.');
    }
}
