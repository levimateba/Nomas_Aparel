<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StockLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLocation::query()
            ->withSum('inventoryStocks as stock_units', 'quantity')
            ->withCount(['inventoryStocks as sku_rows' => fn ($q) => $q->where('quantity', '!=', 0)])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        $locations = $query->get();

        $all = StockLocation::query()
            ->withSum('inventoryStocks as stock_units', 'quantity')
            ->withCount(['inventoryStocks as sku_rows' => fn ($q) => $q->where('quantity', '!=', 0)])
            ->get();

        $stats = [
            'total' => $all->count(),
            'active' => $all->where('is_active', true)->count(),
            'units' => (int) $all->sum('stock_units'),
            'sku_rows' => (int) $all->sum('sku_rows'),
            'active_pct' => $all->count() > 0 ? (int) round(($all->where('is_active', true)->count() / $all->count()) * 100) : 0,
        ];

        return view('admin.inventory.locations.index', compact('locations', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        StockLocation::query()->create($data);

        return back()->with('success', 'Location created.');
    }

    public function update(Request $request, StockLocation $location)
    {
        $data = $this->validated($request, $location);
        $location->update($data);

        return back()->with('success', 'Location updated.');
    }

    public function destroy(StockLocation $location)
    {
        if (! $location->canBeDeleted()) {
            $location->update(['is_active' => false]);

            return back()->with('success', 'Location has history — deactivated instead of deleted.');
        }

        $location->delete();

        return back()->with('success', 'Location deleted.');
    }

    private function validated(Request $request, ?StockLocation $location = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('stock_locations', 'code')->ignore($location?->id),
            ],
            'type' => 'required|in:'.implode(',', array_keys(StockLocation::TYPES)),
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = strtoupper(Str::slug($data['code'], '_'));
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
