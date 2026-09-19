<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductStyle;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductStyleController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);

        $styles = ProductStyle::query()
            ->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->input('q')).'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('admin.product-styles.index', compact('styles'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $name = trim($data['name']);
        $slug = Str::slug($name) ?: Str::slug($name.'-style');

        $style = ProductStyle::query()
            ->where(function ($q) use ($slug, $name) {
                $q->where('slug', $slug)
                    ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
            })
            ->first();

        if ($style) {
            if (! $style->is_active) {
                $style->forceFill(['is_active' => true])->save();
            }
        } else {
            $maxSort = (int) ProductStyle::query()->max('sort_order');
            $style = ProductStyle::create([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $maxSort + 1,
                'is_active' => true,
            ]);
            Audit::log('product_style_created', 'Created product type '.$style->name, $style, [], 'inventory');
        }

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $style->id,
                'name' => $style->name,
            ]);
        }

        return back()->with('success', 'Product type saved.');
    }

    public function destroy(ProductStyle $productStyle): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_products'), 403);
        $name = $productStyle->name;
        $count = $productStyle->products()->count();
        $productStyle->delete();
        Audit::log('product_style_deleted', 'Deleted product type '.$name, null, ['products' => $count], 'inventory');

        return back()->with('success', $count > 0
            ? "Type \"{$name}\" deleted. {$count} product(s) now have no type."
            : "Type \"{$name}\" deleted.");
    }
}
