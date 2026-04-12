<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $query = Service::latest();

        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }

        $services = $query->paginate(10)->withQueryString();
        $serviceCategories = config('content_taxonomy.service_categories', []);

        return view('admin.service.index', compact('services', 'serviceCategories'));
    }

    public function create()
    {
        $serviceCategories = config('content_taxonomy.service_categories', []);

        return view('admin.service.create', compact('serviceCategories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', 'string', Rule::in(config('content_taxonomy.service_categories', []))],
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
        $serviceCategories = config('content_taxonomy.service_categories', []);

        return view('admin.service.edit', compact('service', 'serviceCategories'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', 'string', Rule::in(config('content_taxonomy.service_categories', []))],
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
