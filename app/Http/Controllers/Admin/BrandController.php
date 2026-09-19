<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_description' => ['nullable', 'string', 'max:255'],
        ]);

        $name = trim($data['name']);
        $brand = ProductBrand::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($brand) {
            $brand->forceFill([
                'is_active' => true,
                'short_description' => $data['short_description'] ?? $brand->short_description,
            ])->save();
        } else {
            $brand = ProductBrand::create([
                'name' => $name,
                'short_description' => $data['short_description'] ?? null,
                'is_active' => true,
            ]);
            Audit::log('brand_created', 'Created brand '.$brand->name, $brand, [], 'inventory');
        }

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $brand->id,
                'name' => $brand->name,
            ]);
        }

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
