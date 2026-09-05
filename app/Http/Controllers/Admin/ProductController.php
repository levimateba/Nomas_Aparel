<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductStyle;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\VariantAttribute;
use App\Models\Vendor;
use App\Services\ProductVariantService;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::query()
            ->with(['category', 'vendor', 'brand', 'supplier', 'activeVariants', 'inventoryStocks.location', 'images'])
            ->withCount('activeVariants')
            ->latest();

        if (request()->filled('stock')) {
            if (request('stock') === 'in') {
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        if (Schema::hasColumn('products', 'reorder_level')) {
                            $inner->whereColumn('stock', '>', 'reorder_level');
                        } else {
                            $inner->where('stock', '>', 5);
                        }
                    })->orWhere(function ($inner) {
                        $inner->where('stock', '>', 0);
                        if (Schema::hasColumn('products', 'reorder_level')) {
                            $inner->where('reorder_level', 0)->where('stock', '>', 5);
                        }
                    });
                })->where('stock', '>', 0);
            } elseif (request('stock') === 'low') {
                $query->where('stock', '>', 0);
                if (Schema::hasColumn('products', 'reorder_level')) {
                    $query->where(function ($q) {
                        $q->whereColumn('stock', '<=', 'reorder_level')
                            ->orWhere(function ($inner) {
                                $inner->where('reorder_level', 0)->where('stock', '<=', 5);
                            });
                    });
                } else {
                    $query->where('stock', '<=', 5);
                }
            } elseif (request('stock') === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        if (request()->filled('status')) {
            $query->where('is_active', request('status') === 'active');
        }

        if (request()->filled('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        if (request()->filled('brand_id')) {
            if (request('brand_id') === 'none') {
                $query->whereNull('brand_id');
            } else {
                $query->where('brand_id', request('brand_id'));
            }
        }

        if (request()->filled('q')) {
            $term = trim((string) request('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%'.$term.'%')
                    ->orWhere('sku', 'like', '%'.$term.'%');
                if (Schema::hasColumn('products', 'barcode')) {
                    $builder->orWhere('barcode', 'like', '%'.$term.'%');
                }
                if (Schema::hasColumn('products', 'search_keywords')) {
                    $builder->orWhere('search_keywords', 'like', '%'.$term.'%');
                }
                if (Schema::hasColumn('products', 'batch_lot')) {
                    $builder->orWhere('batch_lot', 'like', '%'.$term.'%');
                }
                if (Schema::hasColumn('products', 'shelf_location')) {
                    $builder->orWhere('shelf_location', 'like', '%'.$term.'%');
                }
                $builder->orWhereHas('brand', fn ($b) => $b->where('name', 'like', '%'.$term.'%'));
                $builder->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%'.$term.'%'));
            });
        }

        $perPage = (int) request('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $products = $query->paginate($perPage)->withQueryString();

        $monthStart = now()->startOfMonth();
        $prevMonthStart = now()->subMonth()->startOfMonth();
        $prevMonthEnd = now()->subMonth()->endOfMonth();
        $createdThis = Product::where('created_at', '>=', $monthStart)->count();
        $createdPrev = Product::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();
        $activeThis = Product::where('is_active', true)->where('created_at', '>=', $monthStart)->count();
        $activePrev = Product::where('is_active', true)->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low' => Product::where('is_active', true)->where('stock', '>', 0)->where(function ($q) {
                if (Schema::hasColumn('products', 'reorder_level')) {
                    $q->whereColumn('stock', '<=', 'reorder_level')
                        ->orWhere(function ($inner) {
                            $inner->where('reorder_level', 0)->where('stock', '<=', 5);
                        });
                } else {
                    $q->where('stock', '<=', 5);
                }
            })->count(),
            'out' => Product::where('stock', '<=', 0)->count(),
            'total_trend' => $this->percentChange($createdPrev, $createdThis),
            'active_trend' => $this->percentChange($activePrev, $activeThis),
            'sparklines' => [
                'total' => $this->dailyCreatedCounts(14),
                'active' => $this->dailyCreatedCounts(14, true),
                'low' => [3, 2, 4, 3, 5, 4, 6, 5, 4, 5, 6, 7, 5, max(1, Product::where('is_active', true)->where('stock', '>', 0)->where('stock', '<=', 5)->count())],
                'out' => [1, 1, 0, 1, 0, 0, 1, 0, 0, 0, 1, 0, 0, Product::where('stock', '<=', 0)->count()],
            ],
        ];

        $brands = Schema::hasTable('product_brands')
            ? ProductBrand::query()->orderBy('name')->get()
            : collect();
        $categories = Category::query()->orderBy('name')->get();
        $stockLocations = Schema::hasTable('stock_locations')
            ? \App\Models\StockLocation::orderedActive()
            : collect();
        $mainStore = \App\Models\StockLocation::mainStore();
        $shopFloor = \App\Models\StockLocation::shopFloor();

        return view('admin.products.index', compact(
            'products', 'stats', 'brands', 'categories', 'stockLocations', 'mainStore', 'shopFloor', 'perPage'
        ));
    }

    private function percentChange(int $previous, int $current): ?int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    private function dailyCreatedCounts(int $days, bool $activeOnly = false): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $driver = Schema::getConnection()->getDriverName();
        $dayExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : 'DATE(created_at)';

        $query = Product::query()
            ->selectRaw("{$dayExpr} as day, COUNT(*) as total")
            ->where('created_at', '>=', $start);
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        $rows = $query->groupBy('day')->pluck('total', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $series[] = (int) ($rows[$day] ?? 0);
        }

        return $series;
    }

    public function create()
    {
        return view('admin.products.create', $this->formContext());
    }

    public function store(Request $request, ProductVariantService $variants)
    {
        $data = $this->validateData($request);
        $data = $this->finalizeProductPayload($request, $data);

        $product = Product::create($data);
        $this->afterProductSave($request, $product, $variants);

        Audit::log('product_created', 'Created product '.$product->name, $product, [
            'sku' => $product->sku,
            'price' => $product->price,
        ], 'inventory');

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->loadMissing(['additionalBarcodes', 'variants.attributeValues', 'images', 'specifications', 'style']);

        return view('admin.products.edit', $this->formContext($product));
    }

    public function update(Request $request, Product $product, ProductVariantService $variants)
    {
        $data = $this->validateData($request, $product);
        $data = $this->finalizeProductPayload($request, $data, $product);

        $product->update($data);
        $this->afterProductSave($request, $product, $variants);

        Audit::log('product_updated', 'Updated product '.$product->name, $product, [
            'sku' => $product->sku,
            'price' => $product->price,
        ], 'inventory');

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        if ($product->is_active && (OrderItem::query()->where('product_id', $product->id)->exists() || (Schema::hasTable('purchase_items') && PurchaseItem::query()->where('product_id', $product->id)->exists()))) {
            $product->update(['is_active' => false]);
            Audit::log('product_archived', 'Archived product '.$name, $product, [], 'inventory');

            return redirect()->route('admin.products.index')->with('success', 'Product archived (linked to sales/purchases).');
        }

        try {
            $product->delete();
            Audit::log('product_deleted', 'Deleted product '.$name, null, ['name' => $name], 'inventory');
        } catch (\Throwable $e) {
            report($e);
            $product->update(['is_active' => false]);
            Audit::log('product_archived', 'Archived product '.$name, $product, [], 'inventory');

            return redirect()->route('admin.products.index')->with('success', 'Product archived.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Product removed.');
    }

    public function activate(Product $product)
    {
        $product->update(['is_active' => true]);
        Audit::log('product_reactivated', 'Reactivated product '.$product->name, $product, [], 'inventory');

        return back()->with('success', 'Product reactivated.');
    }

    public function generateSku(Request $request)
    {
        $name = trim((string) $request->input('name', 'PROD'));
        $base = strtoupper(Str::slug(Str::limit($name, 12, ''), ''));
        $base = $base !== '' ? $base : 'PROD';
        $sku = $base.'-'.now()->format('ymd').'-'.strtoupper(Str::random(4));

        return response()->json(['sku' => $sku]);
    }

    public function exportTemplate()
    {
        $filename = 'products-import-template.csv';

        $headers = [
            'name',
            'sku',
            'barcode',
            'category',
            'brand',
            'supplier',
            'description',
            'buying_price',
            'price',
            'sale_price',
            'wholesale_price',
            'tax_rate',
            'stock',
            'reorder_level',
            'reorder_quantity',
            'unit',
            'product_type',
            'target_audience',
            'shelf_location',
            'batch_lot',
            'weight',
            'search_keywords',
            'care_instructions',
            'is_active',
            'is_featured',
            'allow_online_purchase',
        ];

        $sample = [
            'Sample Unisex Polo',
            'NOM-UNI-001',
            '6001001001001',
            'Uniforms',
            'Nomas',
            '',
            'School unisex polo uniform shirt.',
            '450',
            '890',
            '790',
            '750',
            '16',
            '60',
            '10',
            '20',
            'pcs',
            'product',
            'unisex',
            'SHELF A1',
            'LOT-001',
            '0.25',
            'polo, school, uniform',
            'Machine wash cold. Do not bleach.',
            '1',
            '0',
            '1',
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, $sample);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if ($handle === false) {
            return back()->with('error', 'Could not read the uploaded file.');
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            return back()->with('error', 'CSV is empty.');
        }

        $headers = array_map(fn ($h) => Str::snake(strtolower(trim((string) $h))), $headers);
        $imported = 0;
        $updated = 0;
        $errors = [];
        $rowNum = 1;
        $inventory = app(\App\Services\InventoryStockService::class);
        $main = \App\Models\StockLocation::mainStore();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            try {
                $data = [];
                foreach ($headers as $i => $key) {
                    $data[$key] = isset($row[$i]) ? trim((string) $row[$i]) : '';
                }

                $name = $data['name'] ?? '';
                if ($name === '') {
                    continue;
                }

                $sku = $data['sku'] ?? '';
                if ($sku === '') {
                    $sku = 'NOM-'.strtoupper(Str::random(6));
                }

                $categoryId = null;
                if (! empty($data['category'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $data['category']],
                        ['slug' => Str::slug($data['category']) ?: 'category-'.Str::random(4), 'is_active' => true]
                    );
                    $categoryId = $category->id;
                }

                $brandId = null;
                if (! empty($data['brand']) && Schema::hasTable('product_brands')) {
                    $brand = ProductBrand::firstOrCreate(
                        ['name' => $data['brand']],
                        ['is_active' => true]
                    );
                    $brandId = $brand->id;
                }

                $supplierId = null;
                if (! empty($data['supplier']) && Schema::hasTable('suppliers')) {
                    $supplier = Supplier::query()->where('name', $data['supplier'])->first();
                    $supplierId = $supplier?->id;
                }

                $price = is_numeric($data['price'] ?? null) ? (float) $data['price'] : 0;
                $payload = [
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
                    'sku' => $sku,
                    'barcode' => ($data['barcode'] ?? '') !== '' ? $data['barcode'] : null,
                    'description' => ($data['description'] ?? '') !== '' ? $data['description'] : null,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'supplier_id' => $supplierId,
                    'buying_price' => is_numeric($data['buying_price'] ?? null) ? (float) $data['buying_price'] : null,
                    'price' => $price,
                    'sale_price' => is_numeric($data['sale_price'] ?? null) ? (float) $data['sale_price'] : null,
                    'wholesale_price' => is_numeric($data['wholesale_price'] ?? null) ? (float) $data['wholesale_price'] : null,
                    'tax_rate' => is_numeric($data['tax_rate'] ?? null) ? (float) $data['tax_rate'] : null,
                    'stock' => is_numeric($data['stock'] ?? null) ? (int) $data['stock'] : 0,
                    'reorder_level' => is_numeric($data['reorder_level'] ?? null) ? (int) $data['reorder_level'] : 0,
                    'reorder_quantity' => is_numeric($data['reorder_quantity'] ?? null) ? (int) $data['reorder_quantity'] : null,
                    'unit' => ($data['unit'] ?? '') !== '' ? $data['unit'] : 'pcs',
                    'product_type' => ($data['product_type'] ?? '') !== '' ? $data['product_type'] : 'product',
                    'target_audience' => ($data['target_audience'] ?? '') !== '' ? $data['target_audience'] : null,
                    'shelf_location' => ($data['shelf_location'] ?? '') !== '' ? $data['shelf_location'] : null,
                    'batch_lot' => ($data['batch_lot'] ?? '') !== '' ? $data['batch_lot'] : null,
                    'weight' => is_numeric($data['weight'] ?? null) ? (float) $data['weight'] : null,
                    'search_keywords' => ($data['search_keywords'] ?? '') !== '' ? $data['search_keywords'] : null,
                    'care_instructions' => ($data['care_instructions'] ?? '') !== '' ? $data['care_instructions'] : null,
                    'is_active' => ! isset($data['is_active']) || $data['is_active'] === '' || in_array(strtolower((string) $data['is_active']), ['1', 'true', 'yes', 'active'], true),
                    'is_featured' => isset($data['is_featured']) && in_array(strtolower((string) $data['is_featured']), ['1', 'true', 'yes'], true),
                    'allow_online_purchase' => ! isset($data['allow_online_purchase']) || $data['allow_online_purchase'] === '' || in_array(strtolower((string) $data['allow_online_purchase']), ['1', 'true', 'yes'], true),
                ];

                $existing = Product::query()->where('sku', $sku)->first();
                if ($existing) {
                    unset($payload['slug']);
                    if ($categoryId === null) {
                        unset($payload['category_id']);
                    }
                    $existing->update($payload);
                    $product = $existing->fresh();
                    $updated++;
                    $opening = false;
                } else {
                    $product = Product::create($payload);
                    $imported++;
                    $opening = true;
                }

                if ($inventory->enabled() && $main && ! $product->has_variants && array_key_exists('stock', $data) && $data['stock'] !== '') {
                    $product->loadMissing('inventoryStocks');
                    $qty = max(0, (int) $data['stock']);
                    $locationStock = [];
                    foreach (\App\Models\StockLocation::orderedActive() as $loc) {
                        if ($loc->id === $main->id) {
                            $locationStock[$loc->id] = $qty;
                        } else {
                            $locationStock[$loc->id] = $opening
                                ? 0
                                : (int) optional($product->inventoryStocks->firstWhere('stock_location_id', $loc->id))->quantity;
                        }
                    }
                    $inventory->syncLocationQuantities(
                        $product,
                        null,
                        $locationStock,
                        $opening ? 'OPENING_STOCK' : 'ADJUSTMENT',
                        $opening ? 'CSV import opening stock' : 'CSV import stock update',
                        $opening
                    );
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        $message = "Import finished: {$imported} created, {$updated} updated.";
        if ($errors) {
            $message .= ' '.count($errors).' row(s) failed.';

            return back()->with('success', $message)->with('error', implode(' ', array_slice($errors, 0, 5)));
        }

        return back()->with('success', $message);
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
        $query = Product::query()->with(['category', 'brand', 'supplier'])->latest();
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
                if (Schema::hasColumn('products', 'barcode')) {
                    $builder->orWhere('barcode', 'like', '%' . $term . '%');
                }
            });
        }

        $rows = $query->get();
        $filename = 'products-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'ID', 'Name', 'SKU', 'Barcode', 'Category', 'Brand', 'Supplier',
                'Buying Price', 'Price', 'Sale Price', 'Wholesale Price', 'Tax Rate',
                'Stock', 'Reorder Level', 'Unit', 'Shelf Location', 'Status',
                'Featured', 'Online', 'Created At',
            ]);
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->id,
                    $row->name,
                    $row->sku,
                    $row->barcode ?? '',
                    $row->category?->name,
                    $row->brand?->name,
                    $row->supplier?->name,
                    $row->buying_price,
                    $row->price,
                    $row->sale_price,
                    $row->wholesale_price,
                    $row->tax_rate,
                    $row->stock,
                    $row->reorder_level,
                    $row->unit,
                    $row->shelf_location,
                    $row->is_active ? 'Active' : 'Inactive',
                    $row->is_featured ? 'Yes' : 'No',
                    $row->allow_online_purchase ? 'Yes' : 'No',
                    $row->created_at?->toDateTimeString(),
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        $rules = [
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'brand_id' => 'nullable|exists:product_brands,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
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
            'buying_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_percent' => 'nullable|numeric|min:1|max:99',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'purchase_unit' => 'nullable|string|max:50',
            'purchase_unit_qty' => 'nullable|integer|min:1',
            'product_type' => 'nullable|in:product,service',
            'has_variants' => 'nullable|boolean',
            'target_audience' => 'nullable|string|max:40',
            'product_style_id' => 'nullable|exists:product_styles,id',
            'care_instructions' => 'nullable|string|max:5000',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'requires_shipping' => 'nullable|boolean',
            'shipping_class' => 'nullable|string|max:80',
            'free_shipping' => 'nullable|boolean',
            'store_visibility' => 'nullable|in:visible,hidden',
            'is_featured' => 'nullable|boolean',
            'is_new_arrival' => 'nullable|boolean',
            'is_best_seller' => 'nullable|boolean',
            'allow_online_purchase' => 'nullable|boolean',
            'display_stock' => 'nullable|boolean',
            'allow_backorders' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:500',
            'seo_noindex' => 'nullable|boolean',
            'search_keywords' => 'nullable|string|max:2000',
            'shelf_location' => 'nullable|string|max:120',
            'batch_lot' => 'nullable|string|max:120',
            'expiry_date' => 'nullable|date',
            'image_url' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|max:2048',
            'gallery_files' => 'nullable|array',
            'gallery_files.*' => 'nullable|image|max:2048',
            'gallery_urls' => 'nullable|array',
            'gallery_urls.*' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'additional_barcodes' => 'nullable|array',
            'additional_barcodes.*' => 'nullable|string|max:64',
            'option_names' => 'nullable|array',
            'option_names.*' => 'nullable|string|max:100',
            'option_values' => 'nullable|array',
            'option_values.*' => 'nullable|string|max:100',
            'spec_names' => 'nullable|array',
            'spec_names.*' => 'nullable|string|max:120',
            'spec_values' => 'nullable|array',
            'spec_values.*' => 'nullable|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.barcode' => 'nullable|string|max:64',
            'variants.*.buying_price' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.wholesale_price' => 'nullable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.location_stock' => 'nullable|array',
            'variants.*.location_stock.*' => 'nullable|integer|min:0',
            'variants.*.reorder_level' => 'nullable|integer|min:0',
            'variants.*.is_active' => 'nullable',
            'variants.*.attribute_value_ids' => 'nullable|array',
            'variants.*.attribute_value_ids.*' => 'integer',
            'location_stock' => 'nullable|array',
            'location_stock.*' => 'nullable|integer|min:0',
            'location_reorder' => 'nullable|array',
            'location_reorder.*.reorder_level' => 'nullable|integer|min:0',
            'location_reorder.*.reorder_quantity' => 'nullable|integer|min:0',
        ];

        if (Schema::hasColumn('products', 'barcode')) {
            $rules['barcode'] = [
                'nullable',
                'string',
                'max:64',
                Rule::unique('products', 'barcode')->ignore($product?->id),
            ];
        }

        foreach ([
            'brand_id', 'supplier_id', 'buying_price', 'wholesale_price', 'tax_rate',
            'reorder_level', 'reorder_quantity', 'unit', 'purchase_unit', 'purchase_unit_qty',
            'product_type', 'search_keywords', 'shelf_location', 'batch_lot', 'expiry_date',
            'has_variants', 'target_audience', 'product_style_id', 'care_instructions',
            'weight', 'length', 'width', 'height', 'requires_shipping', 'shipping_class', 'free_shipping',
            'store_visibility', 'is_featured', 'is_new_arrival', 'is_best_seller',
            'allow_online_purchase', 'display_stock', 'allow_backorders',
            'meta_title', 'meta_description', 'meta_keywords', 'seo_noindex',
        ] as $optionalColumn) {
            if (! Schema::hasColumn('products', $optionalColumn) && ! in_array($optionalColumn, ['product_style_id'], true)) {
                unset($rules[$optionalColumn]);
            }
            if ($optionalColumn === 'product_style_id' && ! Schema::hasTable('product_styles')) {
                unset($rules[$optionalColumn]);
            }
        }

        if ($request->boolean('has_variants')) {
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.name'] = 'required|string|max:255';
            $rules['variants.*.price'] = 'required|numeric|min:0';
        }

        $data = $request->validate($rules);

        if ($request->boolean('has_variants')) {
            $this->assertVariantUniqueness($request->input('variants', []), $product?->id);
        }

        $data = $this->applySalePricing($data);
        unset(
            $data['discount_percent'],
            $data['additional_barcodes'],
            $data['option_names'],
            $data['option_values'],
            $data['spec_names'],
            $data['spec_values'],
            $data['variants'],
            $data['gallery_files'],
            $data['gallery_urls'],
            $data['location_stock'],
            $data['location_reorder']
        );

        if (array_key_exists('barcode', $data)) {
            $barcode = trim((string) ($data['barcode'] ?? ''));
            $data['barcode'] = $barcode !== '' ? $barcode : null;
        }
        if (! Schema::hasColumn('products', 'barcode')) {
            unset($data['barcode']);
        }

        $data['stock'] = (int) ($data['stock'] ?? 0);
        $data['reorder_level'] = (int) ($data['reorder_level'] ?? 0);
        $data['reorder_quantity'] = (int) ($data['reorder_quantity'] ?? 0);
        $data['purchase_unit_qty'] = max(1, (int) ($data['purchase_unit_qty'] ?? 1));
        $data['unit'] = $data['unit'] ?? 'pcs';
        $data['purchase_unit'] = $data['purchase_unit'] ?: $data['unit'];
        $data['product_type'] = $data['product_type'] ?? 'product';
        if (! $request->boolean('tracks_expiry')) {
            $data['expiry_date'] = null;
        }

        foreach (array_keys($rules) as $optionalColumn) {
            if (str_contains($optionalColumn, '.') || in_array($optionalColumn, [
                'name', 'price', 'stock', 'image_file', 'image_url', 'is_active', 'description', 'slug', 'sku',
                'discount_percent', 'sale_price', 'category_id', 'vendor_id', 'additional_barcodes',
                'option_names', 'option_values', 'spec_names', 'spec_values', 'variants', 'gallery_files', 'gallery_urls',
            ], true)) {
                continue;
            }
            if (! Schema::hasColumn('products', $optionalColumn)) {
                unset($data[$optionalColumn]);
            }
        }

        return $data;
    }

    private function formContext(?Product $product = null): array
    {
        return [
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'vendors' => Vendor::query()->where('is_active', true)->orderBy('name')->get(),
            'brands' => Schema::hasTable('product_brands')
                ? ProductBrand::query()->where('is_active', true)->orderBy('name')->get()
                : collect(),
            'suppliers' => Schema::hasTable('suppliers')
                ? Supplier::query()->where('is_active', true)->orderBy('name')->get()
                : collect(),
            'styles' => Schema::hasTable('product_styles')
                ? ProductStyle::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()
                : collect(),
            'variantAttributes' => Schema::hasTable('variant_attributes')
                ? VariantAttribute::query()->with(['values' => fn ($q) => $q->where('is_active', true)])->where('is_active', true)->orderBy('sort_order')->get()
                : collect(),
            'audiences' => Product::AUDIENCES,
            'stockLocations' => Schema::hasTable('stock_locations')
                ? \App\Models\StockLocation::orderedActive()
                : collect(),
        ];
    }

    private function finalizeProductPayload(Request $request, array $data, ?Product $product = null): array
    {
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', $product?->is_active ?? true);
        $data['tracks_expiry'] = $request->boolean('tracks_expiry');
        $data['has_variants'] = $request->boolean('has_variants');
        $data['requires_shipping'] = $request->boolean('requires_shipping', true);
        $data['free_shipping'] = $request->boolean('free_shipping');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['allow_online_purchase'] = $request->boolean('allow_online_purchase', true);
        $data['display_stock'] = $request->boolean('display_stock', true);
        $data['allow_backorders'] = $request->boolean('allow_backorders');
        $data['seo_noindex'] = $request->boolean('seo_noindex');
        $data['product_options'] = $this->normalizeOptions($request);
        $data['has_product_options'] = ! empty($data['product_options']);
        $data['image_url'] = $this->resolveImageUrl($request, $product);
        $data['store_visibility'] = $data['store_visibility'] ?? 'visible';
        if (($data['product_type'] ?? 'product') === 'service') {
            $data['stock'] = 0;
            $data['has_variants'] = false;
        }

        return $data;
    }

    private function afterProductSave(Request $request, Product $product, ProductVariantService $variants): void
    {
        $product->syncAdditionalBarcodes($request->input('additional_barcodes', []));

        $specs = [];
        foreach ($request->input('spec_names', []) as $i => $name) {
            $specs[] = ['name' => $name, 'value' => $request->input('spec_values.'.$i)];
        }
        $product->syncSpecifications($specs);

        $galleryUrls = collect($request->input('gallery_urls', []))->filter()->values()->all();
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                if (! $file) {
                    continue;
                }
                $galleryUrls[] = Storage::url($file->store('products', 'public'));
            }
        }
        if ($product->image_url && ! in_array($product->image_url, $galleryUrls, true)) {
            array_unshift($galleryUrls, $product->image_url);
        }
        $product->syncGalleryImages($galleryUrls, $product->image_url);

        $locationIds = Schema::hasTable('stock_locations')
            ? \App\Models\StockLocation::orderedActive()->pluck('id')->all()
            : [];

        $variants->syncVariants(
            $product,
            $request->input('variants', []),
            (bool) $product->has_variants,
            $locationIds
        );

        $inventory = app(\App\Services\InventoryStockService::class);
        if ($inventory->enabled() && ! $product->has_variants && ! $product->isService()) {
            $locationStock = collect($request->input('location_stock', []))
                ->mapWithKeys(fn ($qty, $id) => [(int) $id => max(0, (int) $qty)])
                ->all();

            if (empty($locationStock) && $request->filled('stock')) {
                $main = \App\Models\StockLocation::mainStore();
                if ($main) {
                    $locationStock = [$main->id => max(0, (int) $request->input('stock'))];
                    foreach (\App\Models\StockLocation::orderedActive() as $loc) {
                        $locationStock[$loc->id] = $locationStock[$loc->id] ?? 0;
                    }
                }
            }

            $wasRecentlyCreated = $product->wasRecentlyCreated;
            $inventory->syncLocationQuantities(
                $product,
                null,
                $locationStock,
                $wasRecentlyCreated ? 'OPENING_STOCK' : 'ADJUSTMENT',
                $wasRecentlyCreated ? 'Opening stock' : 'Product stock update',
                $wasRecentlyCreated
            );

            if ($request->filled('location_reorder')) {
                $inventory->syncLocationReorderLevels($product, null, $request->input('location_reorder', []));
            }
        }
    }

    private function assertVariantUniqueness(array $variants, ?int $productId = null): void
    {
        $skus = [];
        $barcodes = [];
        $signatures = [];
        foreach ($variants as $row) {
            $sku = trim((string) ($row['sku'] ?? ''));
            $barcode = trim((string) ($row['barcode'] ?? ''));
            $valueIds = collect($row['attribute_value_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->sort()->values()->all();
            $sig = implode('-', $valueIds);

            if ($sku !== '') {
                if (isset($skus[$sku])) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['variants' => 'Duplicate variant SKU: '.$sku]);
                }
                $skus[$sku] = true;
                $exists = \App\Models\ProductVariant::query()->where('sku', $sku)
                    ->when(! empty($row['id']), fn ($q) => $q->where('id', '!=', (int) $row['id']))
                    ->exists();
                if ($exists || ($productId && Product::query()->where('sku', $sku)->where('id', '!=', $productId)->exists())) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['variants' => 'Variant SKU already in use: '.$sku]);
                }
            }
            if ($barcode !== '') {
                if (isset($barcodes[$barcode])) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['variants' => 'Duplicate variant barcode: '.$barcode]);
                }
                $barcodes[$barcode] = true;
                $exists = \App\Models\ProductVariant::query()->where('barcode', $barcode)
                    ->when(! empty($row['id']), fn ($q) => $q->where('id', '!=', (int) $row['id']))
                    ->exists();
                if ($exists || Product::query()->where('barcode', $barcode)->when($productId, fn ($q) => $q->where('id', '!=', $productId))->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['variants' => 'Variant barcode already in use: '.$barcode]);
                }
            }
            if ($sig !== '') {
                if (isset($signatures[$sig])) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['variants' => 'Duplicate variant combination.']);
                }
                $signatures[$sig] = true;
            }
        }
    }

    private function normalizeOptions(Request $request): array
    {
        $names = $request->input('option_names', []);
        $values = $request->input('option_values', []);
        $options = [];
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $value = trim((string) ($values[$i] ?? ''));
            if ($name === '' && $value === '') {
                continue;
            }
            $options[] = ['name' => $name, 'value' => $value];
        }

        return $options;
    }

    private function applySalePricing(array $data): array
    {
        $price = (float) $data['price'];
        $percent = isset($data['discount_percent']) && $data['discount_percent'] !== null && $data['discount_percent'] !== ''
            ? (float) $data['discount_percent']
            : 0.0;
        $sale = isset($data['sale_price']) && $data['sale_price'] !== null && $data['sale_price'] !== ''
            ? (float) $data['sale_price']
            : 0.0;

        if ($percent > 0 && $percent < 100 && $price > 0) {
            $data['sale_price'] = round($price * (1 - ($percent / 100)), 2);
        } elseif ($sale > 0 && $sale < $price) {
            $data['sale_price'] = round($sale, 2);
        } else {
            $data['sale_price'] = null;
        }

        return $data;
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
