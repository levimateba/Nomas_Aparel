<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AboutSection;

class AboutController extends Controller
{
    public function index()
    {
        $aboutSections = AboutSection::latest()->paginate(10);
        return view('admin.about.index', compact('aboutSections'));
    }

    public function create()
    {
        return view('admin.about.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('about', 'public');
        }

        AboutSection::create($data);
        return redirect()->route('admin.about.index')->with('success', 'About section saved.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.about.index');
    }

    public function edit(string $id)
    {
        $section = AboutSection::findOrFail($id);
        return view('admin.about.edit', compact('section'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $section = AboutSection::findOrFail($id);

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('about', 'public');
        }

        $section->update($data);

        return redirect()->route('admin.about.index')->with('success', 'About section updated.');
    }

    public function destroy(string $id)
    {
        $section = AboutSection::findOrFail($id);
        $section->delete();

        return redirect()->route('admin.about.index')->with('success', 'About section removed.');
    }
}
