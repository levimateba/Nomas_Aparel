@extends('layouts.pos')
@section('title', 'Point of Sale')
@section('pos-stats')
    <span>Today: <strong>{{ $todayCount }}</strong> sales · KES {{ number_format($todayRevenue, 0) }}</span>
@endsection

@push('styles')
<style>
    .pos-wrap { display: grid; grid-template-columns: 1.4fr 420px; min-height: calc(100vh - 62px); }
    .pos-catalog { padding: 16px; }
    .search-row { display: flex; gap: 8px; margin-bottom: 12px; }
    .search-row input, .search-row select {
        flex: 1; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; font: inherit;
    }
    .search-row button { background: #d4af37; border: 0; border-radius: 10px; padding: 0 16px; font-weight: 800; cursor: pointer; }
    .cats { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .cats a {
        background: #fff; border: 1px solid #ececec; border-radius: 999px; padding: 6px 12px; font-size: 13px; font-weight: 700;
    }
    .cats a.on { background: #121212; color: #d4af37; border-color: #121212; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
    .tile {
        background: #fff; border: 1px solid #ececec; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column;
    }
    .tile img, .tile .ph { height: 110px; width: 100%; object-fit: cover; background: #eee; display: block; }
    .tile-body { padding: 10px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .tile-body strong { font-size: 13px; line-height: 1.3; min-height: 34px; }
    .tile-body span { color: #6b7280; font-size: 12px; }
    .tile-body b { font-size: 14px; }
    .tile form { margin-top: auto; }
    .tile button, .cart-side .gold {
        width: 100%; background: #d4af37; border: 0; border-radius: 8px; padding: 8px; font-weight: 800; cursor: pointer;
    }
    .tile button:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
    .cart-side {
        background: #fff; border-left: 1px solid #ececec; padding: 16px; display: flex; flex-direction: column;
        position: sticky; top: 62px; height: calc(100vh - 62px); overflow: auto;
    }
    .cart-item { display: grid; grid-template-columns: 1fr auto; gap: 6px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
    .qty { display: flex; align-items: center; gap: 6px; }
    .qty input { width: 54px; padding: 6px; border: 1px solid #e5e7eb; border-radius: 8px; text-align: center; }
    .qty button, .linkish {
        background: #f3f4f6 !important; width: auto !important; padding: 6px 8px !important; font-size: 13px;
    }
    .totals { margin-top: auto; padding-top: 12px; }
    .totals div { display: flex; justify-content: space-between; margin: 6px 0; }
    .totals .grand { font-size: 22px; font-weight: 800; }
    .pay { display: grid; gap: 8px; margin-top: 10px; }
    .pay input, .pay select, .pay textarea {
        width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 10px; font: inherit;
    }
    .charge { width: 100%; background: #121212; color: #d4af37; border: 0; border-radius: 12px; padding: 14px; font-size: 16px; font-weight: 800; cursor: pointer; margin-top: 8px; }
    @media (max-width: 980px) {
        .pos-wrap { grid-template-columns: 1fr; }
        .cart-side { position: relative; top: 0; height: auto; border-left: 0; border-top: 1px solid #ececec; }
    }
</style>
@endpush

@section('content')
<div class="pos-wrap">
    <section class="pos-catalog">
        <form class="search-row" method="GET" action="{{ route('admin.pos.index') }}">
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or scan barcode / SKU" autofocus autocomplete="off">
            <button type="submit">Search</button>
        </form>
        <div class="cats">
            <a class="{{ !request('category_id') ? 'on' : '' }}" href="{{ route('admin.pos.index', request()->only('q')) }}">All</a>
            @foreach($categories as $category)
                <a class="{{ (string) request('category_id') === (string) $category->id ? 'on' : '' }}" href="{{ route('admin.pos.index', array_filter(['q' => request('q'), 'category_id' => $category->id])) }}">{{ $category->name }}</a>
            @endforeach
        </div>
        <div class="grid">
            @forelse($products as $product)
                <article class="tile">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                    @else
                        <div class="ph"></div>
                    @endif
                    <div class="tile-body">
                        <strong>{{ $product->name }}</strong>
                        <span>{{ $product->barcode ?: $product->sku ?: $product->category?->name }} · {{ $product->stock }} in stock</span>
                        <b>KES {{ number_format($product->currentPrice(), 2) }}</b>
                        <form method="POST" action="{{ route('admin.pos.add', $product) }}">
                            @csrf
                            <button type="submit" @disabled($product->stock < 1)>{{ $product->stock < 1 ? 'Out of stock' : 'Add' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <p>No products match this search.</p>
            @endforelse
        </div>
        <div style="margin-top:14px;">{{ $products->links() }}</div>
    </section>

    <aside class="cart-side">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2 style="margin:0;">Current sale</h2>
            <form method="POST" action="{{ route('admin.pos.clear') }}">
                @csrf
                <button class="linkish" type="submit">Clear</button>
            </form>
        </div>

        @forelse($cart as $item)
            @php $productId = $item['product_id']; @endphp
            <div class="cart-item">
                <div>
                    <strong>{{ $item['name'] }}</strong>
                    <div style="color:#6b7280;font-size:12px;">KES {{ number_format($item['price'], 2) }} each</div>
                    <div class="qty">
                        <form method="POST" action="{{ route('admin.pos.update', $productId) }}">
                            @csrf
                            <input type="hidden" name="qty" value="{{ max(0, $item['qty'] - 1) }}">
                            <button type="submit">−</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pos.update', $productId) }}">
                            @csrf
                            <input type="number" name="qty" min="0" value="{{ $item['qty'] }}" onchange="this.form.submit()">
                        </form>
                        <form method="POST" action="{{ route('admin.pos.update', $productId) }}">
                            @csrf
                            <input type="hidden" name="qty" value="{{ $item['qty'] + 1 }}">
                            <button type="submit">+</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pos.remove', $productId) }}">
                            @csrf
                            <button class="linkish" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
                <div><strong>KES {{ number_format($item['price'] * $item['qty'], 2) }}</strong></div>
            </div>
        @empty
            <p style="color:#6b7280;">Tap a product or scan a SKU to start a sale.</p>
        @endforelse

        <div class="totals">
            @if($totals['coupon_code'])
                <form method="POST" action="{{ route('admin.pos.coupon.remove') }}" style="margin-bottom:8px;">
                    @csrf
                    <button class="linkish" type="submit">Remove coupon {{ $totals['coupon_code'] }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.pos.coupon.apply') }}" class="qty" style="margin-bottom:8px;">
                    @csrf
                    <input type="text" name="coupon_code" placeholder="Coupon" style="flex:1;width:auto;">
                    <button class="gold" type="submit" style="width:auto;padding:8px 10px;">Apply</button>
                </form>
            @endif
            <div><span>Items</span><span>{{ $totals['count'] }}</span></div>
            <div><span>Subtotal</span><span>KES {{ number_format($totals['subtotal'], 2) }}</span></div>
            @if($totals['discount'] > 0)
                <div><span>Discount</span><span>- KES {{ number_format($totals['discount'], 2) }}</span></div>
            @endif
            <div class="grand"><span>Total</span><span>KES {{ number_format($totals['total'], 2) }}</span></div>
        </div>

        <form method="POST" action="{{ route('admin.pos.charge') }}" class="pay">
            @csrf
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Customer name (optional)">
            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Phone (optional)">
            <select name="payment_method" id="payment_method" required>
                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>M-Pesa / Mobile money</option>
                <option value="card" @selected(old('payment_method') === 'card')>Card</option>
                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank transfer</option>
            </select>
            <input id="amount_tendered" type="number" step="0.01" min="0" name="amount_tendered" value="{{ old('amount_tendered') }}" placeholder="Cash received">
            <div id="change-due" style="font-weight:800;color:#15803d;"></div>
            <textarea name="notes" rows="2" placeholder="Note (optional)">{{ old('notes') }}</textarea>
            <button class="charge" type="submit" @disabled(empty($cart))>Complete sale · KES {{ number_format($totals['total'], 2) }}</button>
        </form>
    </aside>
</div>
<script>
    (function () {
        const total = {{ json_encode((float) $totals['total']) }};
        const method = document.getElementById('payment_method');
        const tendered = document.getElementById('amount_tendered');
        const change = document.getElementById('change-due');
        function render() {
            const isCash = method && method.value === 'cash';
            if (tendered) tendered.style.display = isCash ? 'block' : 'none';
            if (!isCash || !tendered) { if (change) change.textContent = ''; return; }
            const paid = Number.parseFloat(tendered.value || '0');
            if (paid >= total) {
                change.textContent = 'Change: KES ' + (paid - total).toFixed(2);
            } else {
                change.textContent = paid > 0 ? 'Still short' : '';
            }
        }
        method && method.addEventListener('change', render);
        tendered && tendered.addEventListener('input', render);
        render();
    })();
</script>
@endsection
