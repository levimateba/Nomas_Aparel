<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->paginate(10);
        return view('admin.service.index', compact('services'));
    }

    public function create()
    {
        return view('admin.service.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'sometimes|nullable|string|max:255',
        ]);

        Service::create($data);
        return redirect()->route('admin.service.index')->with('success', 'Service added.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.service.index');
    }

    public function edit(string $id)
    {
        $service = Service::findOrFail($id);
        return view('admin.service.edit', compact('service'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'sometimes|nullable|string|max:255',
        ]);

        $service = Service::findOrFail($id);
        $service->update($data);

        return redirect()->route('admin.service.index')->with('success', 'Service updated.');
    }

    public function destroy(string $id)
    {
        Service::findOrFail($id)->delete();
        return redirect()->route('admin.service.index')->with('success', 'Service removed.');
    }
}
