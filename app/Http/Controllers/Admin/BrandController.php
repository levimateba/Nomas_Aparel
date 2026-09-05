<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);

        $brands = ProductBrand::query()
            ->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->input('q')).'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:product_brands,name'],
            'short_description' => ['nullable', 'string', 'max:255'],
        ]);

        $brand = ProductBrand::create($data + ['is_active' => true]);
        Audit::log('brand_created', 'Created brand '.$brand->name, $brand, [], 'inventory');

        return back()->with('success', 'Brand saved.');
    }

    public function destroy(ProductBrand $brand): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);
        $name = $brand->name;
        $count = $brand->products()->count();
        $brand->delete();
        Audit::log('brand_deleted', 'Deleted brand '.$name, null, ['products' => $count], 'inventory');

        return back()->with('success', $count > 0
            ? "Brand \"{$name}\" deleted. {$count} product(s) now have no brand."
            : "Brand \"{$name}\" deleted.");
    }
}
