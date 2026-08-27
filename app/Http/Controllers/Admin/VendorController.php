<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::query()->withCount('products')->latest()->paginate(20);
        $stats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('is_active', true)->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'stats'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        Vendor::create($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created.');
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $this->validateData($request, $vendor);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $vendor->update($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return back()->with('success', 'Vendor deleted.');
    }

    private function validateData(Request $request, ?Vendor $vendor = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('vendors', 'slug')->ignore($vendor?->id)],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:80',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
