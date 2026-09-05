@extends('layouts.admin')
@section('title', $stockTake->reference)

@section('page_header')
@php
    $statusPill = match($stockTake->status) {
        'approved'  => 'ta-pill-success',
        'review'    => 'ta-pill-warning',
        'counting'  => 'ta-pill-info',
        'cancelled' => 'ta-pill-muted',
        default     => 'ta-pill-muted',
    };
@endphp
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.stock-takes.index') }}" class="hover:text-brand-500">Stocktake</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">{{ $stockTake->reference }}</span>
        </nav>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stockTake->reference }}</h1>
            <span class="ta-pill {{ $statusPill }}">{{ ucwords(str_replace('_', ' ', $stockTake->status)) }}</span>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $stockTake->stocktake_date?->format('d M Y') ?: $stockTake->created_at->format('d M Y') }}
            · {{ $stockTake->user?->name ?: '—' }}
            @if($stockTake->completedByUser) · Approved by {{ $stockTake->completedByUser->name }} @endif
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-takes.index') }}" class="ta-btn-outline">Back</a>
        @if($stockTake->status === 'draft')
            <form method="POST" action="{{ route('admin.stock-takes.start', $stockTake) }}">@csrf
                <button class="ta-btn" type="submit">Start counting</button>
            </form>
        @endif
        @if($stockTake->status === 'counting')
            <form method="POST" action="{{ route('admin.stock-takes.review', $stockTake) }}">@csrf
                <button class="ta-btn-outline" type="submit">Send for review</button>
            </form>
        @endif
        @if($stockTake->canApprove())
            <form method="POST" action="{{ route('admin.stock-takes.approve', $stockTake) }}" onsubmit="return confirm('Approve this stocktake? Inventory will be adjusted.');">@csrf
                <button class="ta-btn" type="submit">Approve &amp; apply</button>
            </form>
        @endif
        @if($stockTake->isEditable())
            <form method="POST" action="{{ route('admin.stock-takes.cancel', $stockTake) }}" onsubmit="return confirm('Cancel this stock take without changing stock?');">@csrf
                <button class="ta-btn-outline" type="submit">Cancel</button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('content')
@php
    $netValue = (float) $stockTake->positive_variance_value + (float) $stockTake->negative_variance_value;
@endphp

<div class="ta-page" x-data="stockTakeCount()">
    @if($stockTake->notes)
        <p class="ta-muted" style="margin:0;">{{ $stockTake->notes }}</p>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Progress</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($countedCount) }} / {{ number_format($totalItems) }}</p>
            <p class="mt-1 text-xs text-gray-400">Counted items</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Positive variance</p>
            <p class="mt-1 text-2xl font-bold text-success-600">+{{ number_format($positiveQty) }}</p>
            <p class="mt-1 text-xs text-gray-400">KES {{ number_format((float) $stockTake->positive_variance_value, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Negative variance</p>
            <p class="mt-1 text-2xl font-bold text-error-600">−{{ number_format(abs($negativeQty)) }}</p>
            <p class="mt-1 text-xs text-gray-400">KES {{ number_format(abs((float) $stockTake->negative_variance_value), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Net value impact</p>
            <p class="mt-1 text-2xl font-bold {{ $netValue >= 0 ? 'text-success-600' : 'text-error-600' }}">KES {{ number_format($netValue, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">After approval</p>
        </div>
    </div>

    @if($stockTake->isEditable())
        {{-- Scan = exact count+1 · Search = filter list --}}
        <div class="ta-toolbar !items-end">
            <form method="GET" action="{{ route('admin.stock-takes.show', $stockTake) }}" class="flex w-full flex-wrap items-end gap-3">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="ta-field" style="flex:1.2;min-width:180px;">
                    <label>Scan barcode / SKU (count +1)</label>
                    <input type="text" name="scan" class="ta-input" placeholder="Exact SKU or barcode…" autofocus autocomplete="off">
                </div>
                <button class="ta-btn" type="submit">Count +1</button>
            </form>
            <form method="GET" action="{{ route('admin.stock-takes.show', $stockTake) }}" class="flex w-full flex-wrap items-end gap-3">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="ta-field" style="flex:1.4;min-width:200px;">
                    <label>Search products</label>
                    <input type="text" name="q" class="ta-input" value="{{ $q }}" placeholder="Filter by name, SKU, barcode…" autocomplete="off">
                </div>
                <button class="ta-btn-outline" type="submit">Search</button>
                @if($q !== '' || $filter !== 'all')
                    <a href="{{ route('admin.stock-takes.show', $stockTake) }}" class="ta-btn-outline">Show all</a>
                @endif
            </form>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach([
                'all' => 'All',
                'uncounted' => 'Uncounted',
                'counted' => 'Counted',
                'variance' => 'With variance',
            ] as $key => $label)
                <a
                    href="{{ route('admin.stock-takes.show', array_filter(['stockTake' => $stockTake, 'filter' => $key, 'q' => $q ?: null])) }}"
                    class="{{ $filter === $key ? 'ta-btn ta-btn-sm' : 'ta-btn-outline ta-btn-sm' }}"
                >{{ $label }}</a>
            @endforeach
            <span class="ml-auto text-sm text-gray-500">Showing {{ $items->count() }} of {{ $totalItems }}</span>
        </div>

        <form method="POST" action="{{ route('admin.stock-takes.update', $stockTake) }}" id="counts-form">
            @csrf
            @method('PUT')
            <div class="ta-table-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Physical counts</h3>
                        @if($q !== '')
                            <p class="text-sm text-gray-500">Filtered by “{{ $q }}”</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="ta-btn-outline ta-btn-sm" @click="matchVisibleSystem" title="Set visible uncounted rows to system qty">
                            Match system (visible)
                        </button>
                        <button type="button" class="ta-btn-outline ta-btn-sm" @click="matchAllSystem" title="Set all uncounted rows to system qty">
                            Match system (all uncounted)
                        </button>
                        <button class="ta-btn" type="submit">Save counts</button>
                    </div>
                </div>
                <div class="ta-table-wrap">
                    <table class="ta-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-right">System</th>
                                <th style="min-width:180px;">Physical qty</th>
                                <th>Variance</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $index => $item)
                                <tr
                                    id="item-{{ $item->id }}"
                                    class="stocktake-row {{ $highlightId === $item->id ? 'bg-brand-50 dark:bg-brand-500/10' : '' }}"
                                    style="{{ $item->isCounted() && $item->variance !== 0 ? ($item->variance > 0 ? 'background:#f0fdf4;' : 'background:#fef2f2;') : '' }}"
                                    data-system="{{ (int) $item->system_qty }}"
                                    data-counted="{{ $item->isCounted() ? '1' : '0' }}"
                                >
                                    <td><div class="ta-name">{{ $item->product_name }}</div></td>
                                    <td class="ta-muted">{{ $item->sku ?: $item->barcode ?: '—' }}</td>
                                    <td class="text-right font-medium">{{ number_format($item->system_qty) }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <div class="flex items-center gap-1">
                                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" @click="bump($refs['qty{{ $item->id }}'], -1)">−</button>
                                            <input
                                                type="number"
                                                min="0"
                                                name="items[{{ $index }}][counted_qty]"
                                                class="ta-input count-input"
                                                style="max-width:90px;margin:0;"
                                                value="{{ old("items.$index.counted_qty", $item->counted_qty) }}"
                                                x-ref="qty{{ $item->id }}"
                                                data-item-id="{{ $item->id }}"
                                            >
                                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" @click="bump($refs['qty{{ $item->id }}'], 1)">+</button>
                                            <button type="button" class="ta-btn-outline ta-btn-sm" @click="matchOne($refs['qty{{ $item->id }}'], {{ (int) $item->system_qty }})" title="Set to system qty">=</button>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->isCounted())
                                            <strong style="color: {{ $item->variance > 0 ? '#15803d' : ($item->variance < 0 ? '#b91c1c' : 'inherit') }};">
                                                {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance) }}
                                            </strong>
                                            <br><span class="ta-muted">KES {{ number_format((float) $item->variance_value, 2) }}</span>
                                        @else
                                            <span class="ta-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][reason]" class="ta-input" value="{{ old("items.$index.reason", $item->reason) }}" placeholder="Optional" style="margin:0;min-width:8rem;">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="ta-empty">
                                        @if($q !== '')
                                            No products match “{{ $q }}”. Try another search or <a class="text-brand-600 underline" href="{{ route('admin.stock-takes.show', $stockTake) }}">show all</a>.
                                        @else
                                            No items in this view.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Keep off-filter items so Save doesn't wipe their counts --}}
                @php $visibleIds = $items->pluck('id')->all(); @endphp
                @foreach($stockTake->items as $hiddenIndex => $hiddenItem)
                    @if(! in_array($hiddenItem->id, $visibleIds, true))
                        <input type="hidden" name="items[{{ 10000 + $hiddenIndex }}][id]" value="{{ $hiddenItem->id }}">
                        <input type="hidden" name="items[{{ 10000 + $hiddenIndex }}][counted_qty]" value="{{ $hiddenItem->counted_qty }}">
                        <input type="hidden" name="items[{{ 10000 + $hiddenIndex }}][reason]" value="{{ $hiddenItem->reason }}">
                    @endif
                @endforeach
            </div>
        </form>
    @else
        <div class="ta-toolbar">
            <form method="GET" action="{{ route('admin.stock-takes.show', $stockTake) }}" class="flex w-full flex-wrap items-end gap-3">
                <div class="ta-field" style="flex:2;">
                    <label>Search</label>
                    <input type="text" name="q" class="ta-input" value="{{ $q }}" placeholder="Filter by name, SKU, barcode…">
                </div>
                <button class="ta-btn" type="submit">Search</button>
                @if($q !== '')
                    <a href="{{ route('admin.stock-takes.show', $stockTake) }}" class="ta-btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="ta-table-card">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Physical counts</h3>
            </div>
            <div class="ta-table-wrap">
                <table class="ta-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>System</th>
                            <th>Physical</th>
                            <th>Variance</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <div class="ta-name">{{ $item->product_name }}</div>
                                    <div class="ta-muted">{{ $item->sku ?: $item->barcode ?: '—' }}</div>
                                </td>
                                <td>{{ number_format($item->system_qty) }}</td>
                                <td>{{ $item->isCounted() ? number_format($item->counted_qty) : '—' }}</td>
                                <td>
                                    @if($item->isCounted())
                                        <strong style="color: {{ $item->variance > 0 ? '#15803d' : ($item->variance < 0 ? '#b91c1c' : 'inherit') }};">
                                            {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance) }}
                                        </strong>
                                    @else — @endif
                                </td>
                                <td>{{ $item->reason ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="ta-empty">No matching products.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
function stockTakeCount() {
    return {
        bump(el, delta) {
            if (!el) return;
            const next = Math.max(0, (parseInt(el.value || '0', 10) || 0) + delta);
            el.value = next;
        },
        matchOne(el, systemQty) {
            if (!el) return;
            el.value = systemQty;
        },
        matchVisibleSystem() {
            document.querySelectorAll('.stocktake-row .count-input').forEach((input) => {
                if (input.value === '' || input.value === null) {
                    const row = input.closest('.stocktake-row');
                    input.value = row?.dataset.system || 0;
                }
            });
        },
        matchAllSystem() {
            if (!confirm('Set every empty count on this screen to its system quantity? Click Save counts afterwards.')) return;
            this.matchVisibleSystem();
        }
    };
}
@if($highlightId)
document.getElementById('item-{{ $highlightId }}')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
@endif
</script>
@endsection
