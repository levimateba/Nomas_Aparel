<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockTransfer;
use App\Services\InventoryStockService;
use App\Services\StockTransferService;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $query = StockTransfer::query()
            ->with(['fromLocation', 'toLocation', 'user', 'items.product'])
            ->latest();

        if (request()->filled('q')) {
            $term = '%'.trim((string) request('q')).'%';
            $query->where(function ($q) use ($term) {
                $q->where('transfer_number', 'like', $term)
                    ->orWhere('reason', 'like', $term)
                    ->orWhereHas('items.product', fn ($p) => $p->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            });
        }

        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }
        if (request()->filled('from_location_id')) {
            $query->where('from_location_id', (int) request('from_location_id'));
        }
        if (request()->filled('to_location_id')) {
            $query->where('to_location_id', (int) request('to_location_id'));
        }
        if (request()->filled('status')) {
            $query->where('status', strtolower((string) request('status')));
        }

        $perPage = (int) request('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $transfers = $query->paginate($perPage)->withQueryString();
        $locations = StockLocation::orderedActive();

        $statusCounts = StockTransfer::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $statusCounts->sum();
        $completed = (int) ($statusCounts['completed'] ?? 0);
        $pending = (int) (($statusCounts['pending'] ?? 0) + ($statusCounts['draft'] ?? 0) + ($statusCounts['approved'] ?? 0));
        $cancelled = (int) ($statusCounts['cancelled'] ?? 0);

        $prevMonthTotal = StockTransfer::query()
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $thisMonthTotal = StockTransfer::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $stats = [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'completed_pct' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'pending_pct' => $total > 0 ? (int) round(($pending / $total) * 100) : 0,
            'cancelled_pct' => $total > 0 ? (int) round(($cancelled / $total) * 100) : 0,
            'trend' => $this->trendPercent($thisMonthTotal, $prevMonthTotal),
        ];

        return view('admin.inventory.transfers.index', compact('transfers', 'perPage', 'locations', 'stats'));
    }

    public function create(InventoryStockService $inventory)
    {
        $locations = StockLocation::orderedActive();
        $fromId = (int) request('from_location_id', StockLocation::mainStore()?->id);
        $toId = (int) request('to_location_id', StockLocation::shopFloor()?->id);
        $product = request('product_id') ? Product::query()->with('activeVariants')->find(request('product_id')) : null;
        $variantId = request('product_variant_id');
        $available = null;
        if ($product && $fromId) {
            $variant = $variantId ? $product->activeVariants->firstWhere('id', (int) $variantId) : null;
            if (! $product->usesVariants() || $variant) {
                $available = $inventory->quantityAt($product, $variant, $fromId);
            }
        }

        return view('admin.inventory.transfers.create', [
            'locations' => $locations,
            'reasons' => StockTransfer::REASONS,
            'fromId' => $fromId,
            'toId' => $toId,
            'product' => $product,
            'variantId' => $variantId,
            'available' => $available,
            'products' => Product::query()->where('is_active', true)->where(function ($q) {
                $q->whereNull('product_type')->orWhere('product_type', '!=', 'service');
            })->orderBy('name')->limit(500)->get(['id', 'name', 'sku', 'barcode', 'has_variants']),
        ]);
    }

    public function store(Request $request, StockTransferService $transfers)
    {
        $data = $request->validate([
            'from_location_id' => 'required|exists:stock_locations,id',
            'to_location_id' => 'required|exists:stock_locations,id|different:from_location_id',
            'reason' => 'required|string|max:120',
            'notes' => 'nullable|string|max:2000',
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'confirm' => 'accepted',
        ]);

        $transfer = $transfers->create([
            'from_location_id' => $data['from_location_id'],
            'to_location_id' => $data['to_location_id'],
            'reason' => $data['reason'],
            'notes' => $data['notes'] ?? null,
            'items' => [[
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'quantity' => $data['quantity'],
            ]],
        ], true);

        Audit::log(
            'stock_transferred',
            'Transfer '.$transfer->transfer_number.' completed ('.$data['reason'].')',
            $transfer,
            [
                'from' => $transfer->from_location_id,
                'to' => $transfer->to_location_id,
                'quantity' => $data['quantity'],
                'product_id' => $data['product_id'],
            ],
            'inventory'
        );

        return redirect()
            ->route('admin.stock-transfers.show', $transfer)
            ->with('success', 'Transfer '.$transfer->transfer_number.' completed successfully.');
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['items.product', 'items.variant', 'fromLocation', 'toLocation', 'user', 'completedByUser']);

        return view('admin.inventory.transfers.show', ['transfer' => $stockTransfer]);
    }

    public function available(Request $request, InventoryStockService $inventory)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'location_id' => 'required|exists:stock_locations,id',
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = $product->variants()->whereKey($data['product_variant_id'])->firstOrFail();
        }

        return response()->json([
            'available' => $inventory->quantityAt($product, $variant, (int) $data['location_id']),
            'variants' => $product->usesVariants()
                ? $product->activeVariants()->get(['id', 'name', 'sku', 'stock'])->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'stock' => $inventory->quantityAt($product, $v, (int) $data['location_id']),
                ])
                : [],
        ]);
    }

    private function trendPercent(int|float $current, int|float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
