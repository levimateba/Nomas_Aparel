<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::query()->with('category')->latest();
        if (request()->filled('stock')) {
            if (request('stock') === 'low') {
                $query->where('stock', '<=', 5);
            } elseif (request('stock') === 'out') {
                $query->where('stock', 0);
            }
        }
        if (request()->filled('status')) {
            $query->where('is_active', request('status') === 'active');
        }
        if (request()->filled('q')) {
            $term = trim((string) request('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('sku', 'like', '%' . $term . '%');
            });
        }

        $products = $query->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['image_url'] = $this->resolveImageUrl($request);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'vendors'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['image_url'] = $this->resolveImageUrl($request, $product);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product removed.');
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        $query = Product::query()->whereIn('id', $data['product_ids']);

        if ($data['action'] === 'activate') {
            $query->update(['is_active' => true]);
            $message = 'Selected products activated.';
        } elseif ($data['action'] === 'deactivate') {
            $query->update(['is_active' => false]);
            $message = 'Selected products deactivated.';
        } else {
            $query->delete();
            $message = 'Selected products deleted.';
        }

        return back()->with('success', $message);
    }

    public function exportCsv(Request $request)
    {
        $query = Product::query()->with('category')->latest();
        if ($request->filled('stock')) {
            if ($request->string('stock')->toString() === 'low') {
                $query->where('stock', '<=', 5);
            } elseif ($request->string('stock')->toString() === 'out') {
                $query->where('stock', 0);
            }
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('sku', 'like', '%' . $term . '%');
            });
        }

        $rows = $query->get();
        $filename = 'products-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'SKU', 'Category', 'Price', 'Sale Price', 'Stock', 'Status', 'Created At']);
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->id,
                    $row->name,
                    $row->sku,
                    $row->category?->name,
                    $row->price,
                    $row->sale_price,
                    $row->stock,
                    $row->is_active ? 'Active' : 'Inactive',
                    $row->created_at?->toDateTimeString(),
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|max:4096',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function resolveImageUrl(Request $request, ?Product $product = null): ?string
    {
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            return Storage::url($path);
        }

        return $request->input('image_url') ?: $product?->image_url;
    }
}
