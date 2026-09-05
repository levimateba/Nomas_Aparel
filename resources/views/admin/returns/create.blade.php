@extends('layouts.admin')
@section('title', 'Process Return')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.returns.index') }}" class="hover:text-brand-500">Returns</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Process Return</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Process Return</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Look up a POS receipt, choose quantities, and restore stock.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.returns.index') }}" class="ta-btn-outline">All returns</a>
    </div>
</div>
@endsection

@push('styles')
<style>
.ret-qty {
    display: inline-flex;
    align-items: center;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.ret-qty button {
    width: 36px; height: 36px;
    border: 0; background: #f3f4f6;
    font-size: 16px; font-weight: 700; color: #111827;
    cursor: pointer; padding: 0;
}
.ret-qty button:hover { background: #e5e7eb; }
.ret-qty button:disabled { opacity: .4; cursor: not-allowed; }
.ret-qty input {
    width: 56px; height: 36px;
    border: 0; text-align: center;
    font-weight: 700; font-size: 14px;
    outline: none; background: #fff;
}
.ret-sticky {
    position: sticky; bottom: 12px; z-index: 30;
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
    margin-top: 8px; padding: 14px 18px;
    border: 1px solid #e5e7eb; border-radius: 16px;
    background: rgba(255,255,255,.97); backdrop-filter: blur(8px);
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
}
</style>
@endpush

@section('content')
@php
    $receiptQuery = trim((string) request('receipt', ''));
    $searched = request()->filled('receipt') || request()->filled('order_id');
@endphp

<div class="ta-page" x-data="returnForm()">
    {{-- Lookup --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Find sale</h2>
        <p class="mt-1 text-sm text-gray-500">Scan or type the POS receipt number (e.g. POS-20260905-0001).</p>
        <form method="GET" action="{{ route('admin.returns.create') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="ta-field flex-1">
                <label for="receipt">Receipt number</label>
                <input
                    id="receipt"
                    name="receipt"
                    class="ta-input"
                    value="{{ $receiptQuery }}"
                    placeholder="POS-…"
                    autofocus
                    autocomplete="off"
                >
            </div>
            <button type="submit" class="ta-btn shrink-0">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                Find sale
            </button>
        </form>

        @if($searched && ! $order)
            <div class="mt-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700">
                No sale found for <strong>{{ $receiptQuery ?: 'that lookup' }}</strong>. Check the receipt number and try again.
            </div>
        @endif

        @if(! $order && ($recentSales ?? collect())->isNotEmpty())
            <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Recent POS sales</p>
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                    @foreach($recentSales as $sale)
                        <a
                            href="{{ route('admin.returns.create', ['receipt' => $sale->order_number]) }}"
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 px-4 py-3 no-underline transition hover:border-brand-500 hover:bg-brand-50/40 dark:border-gray-800"
                        >
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ $sale->order_number }}</div>
                                <div class="truncate text-xs text-gray-500">
                                    {{ $sale->customer_name ?: 'Walk-in' }} · {{ $sale->created_at?->format('d M Y H:i') }}
                                </div>
                            </div>
                            <div class="shrink-0 text-sm font-bold text-gray-800 dark:text-white/90">
                                KES {{ number_format((float) $sale->total_amount, 2) }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($order)
        {{-- Sale summary --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $order->order_number }}</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $order->customer_name ?: 'Walk-in Customer' }}
                        · {{ $order->created_at?->format('d M Y H:i') }}
                        · {{ ucwords(str_replace('_', ' ', (string) $order->payment_method)) }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sale total</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-white/90">KES {{ number_format((float) $order->total_amount, 2) }}</div>
                    @if($order->isReturnable())
                        <span class="ta-pill-success mt-1 inline-flex">Returnable</span>
                    @else
                        <span class="ta-pill-error mt-1 inline-flex">Not returnable</span>
                    @endif
                </div>
            </div>

            @unless($order->isReturnable())
                <div class="p-5">
                    <div class="rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800">
                        This sale cannot be returned (already fully returned, cancelled, or not a POS sale).
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('admin.returns.store') }}" class="flex flex-col gap-5 p-5" @submit="return validateReturn($event)">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="ta-field">
                            <label for="refund_method">Refund method *</label>
                            <select id="refund_method" name="refund_method" class="ta-select" required>
                                <option value="original" @selected(old('refund_method', 'original') === 'original')>Original payment</option>
                                <option value="cash" @selected(old('refund_method') === 'cash')>Cash</option>
                                <option value="mobile_money" @selected(old('refund_method') === 'mobile_money')>M-Pesa</option>
                            </select>
                        </div>
                        <div class="ta-field">
                            <label for="stock_location_id">Return stock to location *</label>
                            <select id="stock_location_id" name="stock_location_id" class="ta-select" required>
                                @foreach(\App\Models\StockLocation::orderedActive() as $loc)
                                    <option
                                        value="{{ $loc->id }}"
                                        @selected((int) old('stock_location_id', $order->stock_location_id ?: \App\Models\StockLocation::shopFloor()?->id) === (int) $loc->id)
                                    >{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400">Usually the Shop where the sale was made.</p>
                        </div>
                        <div class="ta-field md:col-span-2">
                            <label for="reason">Reason *</label>
                            <textarea id="reason" name="reason" rows="2" class="ta-input" required placeholder="e.g. Wrong size, customer changed mind">{{ old('reason') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Items to return</h3>
                                <p class="text-xs text-gray-500">Set quantity for each line. Use Max to return everything still available.</p>
                            </div>
                            <button type="button" class="ta-btn-outline ta-btn-sm" @click="returnAll()">Return all available</button>
                        </div>

                        <div class="ta-table-card overflow-hidden">
                            <div class="ta-table-wrap">
                                <table class="ta-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-right">Sold</th>
                                            <th class="text-right">Already returned</th>
                                            <th class="text-right">Available</th>
                                            <th style="min-width:160px;">Return qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($order->items as $index => $item)
                                        @php $max = $item->returnableQuantity(); @endphp
                                        <tr>
                                            <td>
                                                <div class="ta-name">{{ $item->product_name }}</div>
                                                <div class="text-xs text-gray-400">KES {{ number_format((float) $item->unit_price, 2) }} each</div>
                                                <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
                                            </td>
                                            <td class="text-right">{{ $item->quantity }}</td>
                                            <td class="text-right">{{ $item->returned_quantity }}</td>
                                            <td class="text-right font-semibold">{{ $max }}</td>
                                            <td>
                                                @if($max <= 0)
                                                    <span class="text-xs font-semibold text-gray-400">Fully returned</span>
                                                    <input type="hidden" name="items[{{ $index }}][quantity]" value="0">
                                                @else
                                                    <div class="ret-qty" data-max="{{ $max }}">
                                                        <button type="button" @click="stepQty({{ $index }}, -1)" aria-label="Decrease">−</button>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            max="{{ $max }}"
                                                            name="items[{{ $index }}][quantity]"
                                                            x-model.number="qtys[{{ $index }}]"
                                                            @change="clampQty({{ $index }})"
                                                        >
                                                        <button type="button" @click="stepQty({{ $index }}, 1)" aria-label="Increase">+</button>
                                                    </div>
                                                    <button type="button" class="mt-1 text-xs font-semibold text-brand-600 hover:underline" @click="qtys[{{ $index }}] = {{ $max }}">Max {{ $max }}</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="ret-sticky">
                        <div>
                            <div class="text-xs text-gray-500">Units selected</div>
                            <div class="text-lg font-bold text-gray-800 dark:text-white/90" x-text="totalQty() + ' item(s)'">0 item(s)</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.returns.create') }}" class="ta-btn-outline">Clear</a>
                            <button type="submit" class="ta-btn" :disabled="totalQty() < 1">
                                Process return
                            </button>
                        </div>
                    </div>
                </form>
            @endunless
        </div>
    @endif
</div>

<script>
function returnForm() {
    const maxes = @json(
        collect($order?->items ?? [])->map(fn ($item) => $item->returnableQuantity())->values()
    );
    const initial = maxes.map(() => 0);

    return {
        qtys: initial.slice(),
        maxes: maxes.slice(),
        stepQty(index, delta) {
            const max = Number(this.maxes[index] || 0);
            if (max <= 0) return;
            const next = Math.max(0, Math.min(max, (Number(this.qtys[index]) || 0) + delta));
            this.qtys[index] = next;
        },
        clampQty(index) {
            const max = Number(this.maxes[index] || 0);
            let v = Number(this.qtys[index]) || 0;
            if (v < 0) v = 0;
            if (v > max) v = max;
            this.qtys[index] = v;
        },
        returnAll() {
            this.qtys = this.maxes.map(m => Number(m) || 0);
        },
        totalQty() {
            return this.qtys.reduce((sum, q) => sum + (Number(q) || 0), 0);
        },
        validateReturn(e) {
            if (this.totalQty() < 1) {
                e.preventDefault();
                alert('Select at least one item quantity to return.');
                return false;
            }
            return true;
        },
    };
}
</script>
@endsection
