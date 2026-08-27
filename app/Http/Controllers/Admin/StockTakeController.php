<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTake;
use App\Models\Vendor;
use App\Services\StockTakeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTakeController extends Controller
{
    public function index(): View
    {
        $takes = StockTake::query()
            ->with(['user', 'completedByUser', 'items'])
            ->latest()
            ->paginate(20);

        $stats = [
            'open' => StockTake::query()->whereIn('status', [StockTake::STATUS_DRAFT, StockTake::STATUS_COUNTING])->count(),
            'review' => StockTake::query()->where('status', StockTake::STATUS_REVIEW)->count(),
            'approved' => StockTake::query()->where('status', StockTake::STATUS_APPROVED)->count(),
            'month_net' => (float) StockTake::query()
                ->where('status', StockTake::STATUS_APPROVED)
                ->where('completed_at', '>=', now()->startOfMonth())
                ->get()
                ->sum(fn (StockTake $take) => (float) $take->positive_variance_value + (float) $take->negative_variance_value),
        ];

        return view('admin.stock-takes.index', compact('takes', 'stats'));
    }

    public function create(): View
    {
        return view('admin.stock-takes.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'vendors' => Vendor::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'barcode']),
        ]);
    }

    public function store(Request $request, StockTakeService $stockTakes): RedirectResponse
    {
        $data = $request->validate([
            'stocktake_date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'product_id' => 'nullable|exists:products,id',
            'stock_status' => 'nullable|in:all,low,out,in',
            'notes' => 'nullable|string|max:1000',
        ]);

        $take = $stockTakes->create($data);

        return redirect()
            ->route('admin.stock-takes.show', $take)
            ->with('success', 'Stock take created. Enter physical counts — stock will not change until approval.');
    }

    public function show(Request $request, StockTake $stockTake, StockTakeService $stockTakes): View|RedirectResponse
    {
        $stockTake->load(['user', 'completedByUser', 'category', 'vendor']);

        $query = trim((string) $request->input('q', ''));
        if ($query !== '' && $stockTake->isEditable()) {
            $product = Product::findActiveByScan($query);
            if ($product) {
                $item = $stockTakes->addOrIncrement($stockTake, $product);

                return redirect()
                    ->route('admin.stock-takes.show', $stockTake)
                    ->with('success', $item->product_name . ' counted as ' . $item->counted_qty . '.');
            }
        }

        $stockTake->load('items');
        $items = $stockTake->items->sortBy('product_name')->values();
        $positiveQty = (int) $items->where('variance', '>', 0)->sum('variance');
        $negativeQty = (int) $items->where('variance', '<', 0)->sum('variance');

        return view('admin.stock-takes.show', compact('stockTake', 'items', 'positiveQty', 'negativeQty'));
    }

    public function update(Request $request, StockTake $stockTake, StockTakeService $stockTakes): RedirectResponse
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.counted_qty' => 'nullable|integer|min:0',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        $stockTakes->saveCounts($stockTake, $data['items']);

        return back()->with('success', 'Physical counts saved. Inventory is unchanged until this stock take is approved.');
    }

    public function start(StockTake $stockTake, StockTakeService $stockTakes): RedirectResponse
    {
        $stockTakes->start($stockTake);

        return back()->with('success', 'Counting started.');
    }

    public function review(StockTake $stockTake, StockTakeService $stockTakes): RedirectResponse
    {
        $stockTakes->submitForReview($stockTake);

        return back()->with('success', 'Stock take sent for review.');
    }

    public function approve(StockTake $stockTake, StockTakeService $stockTakes): RedirectResponse
    {
        $stockTakes->approve($stockTake);

        return back()->with('success', 'Stock take approved. Inventory adjustments have been applied.');
    }

    public function cancel(StockTake $stockTake, StockTakeService $stockTakes): RedirectResponse
    {
        $stockTakes->cancel($stockTake);

        return redirect()
            ->route('admin.stock-takes.index')
            ->with('success', 'Stock take cancelled. System stock was not changed.');
    }
}
