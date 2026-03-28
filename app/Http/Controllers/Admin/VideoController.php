<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->paginate(10);
        return view('admin.video.index', compact('videos'));
    }

    public function create()
    {
        return view('admin.video.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'video_path' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:20480',
            'video_url' => 'nullable|url|max:1000',
        ]);

        if (! $request->hasFile('video_path') && ! $request->filled('video_url')) {
            return back()->withErrors(['video_path' => 'Please upload a video file or provide a video URL.'])->withInput();
        }

        if ($request->hasFile('video_path')) {
            $data['video_path'] = $request->file('video_path')->store('videos', 'public');
        }

        Video::create($data);

        return redirect()->route('admin.video.index')->with('success', 'Video created.');
    }

    public function edit(Video $video)
    {
        return view('admin.video.edit', compact('video'));
    }

    public function update(Request $request, Video $video)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'video_path' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:20480',
            'video_url' => 'nullable|url|max:1000',
        ]);

        if ($request->hasFile('video_path')) {
            $data['video_path'] = $request->file('video_path')->store('videos', 'public');
        }

        $video->update($data);

        return redirect()->route('admin.video.index')->with('success', 'Video updated.');
    }

    public function destroy(Video $video)
    {
        $video->delete();
        return redirect()->route('admin.video.index')->with('success', 'Video deleted.');
    }
}
