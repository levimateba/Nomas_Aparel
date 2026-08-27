@extends('layouts.admin')
@section('title', 'New Stocktake')
@section('heading', 'New Stocktake')
@section('subheading', 'Create a new physical stock count session. Stock levels will remain unchanged until approval.')

@push('styles')
<style>
.nstk-page { display: grid; grid-template-columns: minmax(0,1.5fr) 300px; gap: 20px; align-items: start; }
@media(max-width:900px) { .nstk-page { grid-template-columns: 1fr; } }

.nstk-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.nstk-card-head {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 20px; border-bottom: 1px solid #eaecf0; background: #fafafa;
}
.nstk-card-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.nstk-card-icon.gold { background: #fffbeb; color: #d97706; }
.nstk-card-icon svg { width: 20px; height: 20px; }
.nstk-card-title { margin: 0; font-size: 15px; font-weight: 700; color: #111827; }
.nstk-card-sub   { font-size: 12px; color: #6b7280; margin-top: 2px; }
.nstk-card-body  { padding: 20px; display: grid; gap: 18px; }

.nstk-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 6px; }
.nstk-label .req { color: #ef4444; }
.nstk-input {
    width: 100%; padding: 10px 12px;
    border: 1px solid #d1d5db; border-radius: 12px;
    font-size: 14px; color: #111827; outline: none;
    transition: border-color .15s, box-shadow .15s;
    margin: 0 !important;
}
.nstk-input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.nstk-help  { font-size: 11.5px; color: #9ca3af; margin-top: 5px; }
.nstk-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:600px) { .nstk-grid-2 { grid-template-columns: 1fr; } }

/* Select with icon prefix */
.nstk-sel-wrap { position: relative; }
.nstk-sel-wrap .nstk-sel-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.nstk-sel-wrap select { padding-left: 34px !important; }

.nstk-important {
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;
    padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start;
}
.nstk-important-icon { width: 20px; height: 20px; flex-shrink: 0; color: #3b82f6; margin-top: 1px; }
.nstk-important h5 { margin: 0 0 4px; font-size: 13px; font-weight: 700; color: #1e40af; }
.nstk-important p  { margin: 0; font-size: 12.5px; color: #1e40af; }

/* Bottom actions */
.nstk-actions { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eaecf0; background: #fafafa; }
.nstk-btn-back {
    background: #fff; border: 1px solid #d1d5db; border-radius: 10px;
    padding: 10px 20px; font-size: 14px; font-weight: 700; color: #374151;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
}
.nstk-btn-back:hover { background: #f9fafb; }
.nstk-btn-create {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 10px; padding: 10px 24px;
    font-size: 14px; font-weight: 800; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 12px rgba(212,175,55,.3);
}
.nstk-btn-create:hover { opacity: .9; }

/* Right panel */
.nstk-panel {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05);
    position: sticky; top: 88px;
}
@media(max-width:900px) { .nstk-panel { position: static; } }
.nstk-panel-head { padding: 16px 20px; border-bottom: 1px solid #eaecf0; background: #fafafa; }
.nstk-panel-head h4 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
.nstk-steps { padding: 16px 20px; display: grid; gap: 16px; }
.nstk-step { display: flex; gap: 12px; align-items: flex-start; }
.nstk-step-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.nstk-step-icon svg { width: 16px; height: 16px; }
.nstk-step-icon.gold   { background: #fffbeb; color: #d97706; }
.nstk-step-icon.purple { background: #f3f0ff; color: #7c3aed; }
.nstk-step-icon.blue   { background: #eff6ff; color: #3b82f6; }
.nstk-step-icon.green  { background: #ecfdf5; color: #059669; }
.nstk-step h5 { margin: 0 0 3px; font-size: 13px; font-weight: 700; color: #111827; }
.nstk-step p  { margin: 0; font-size: 12px; color: #6b7280; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.stock-takes.store') }}">
@csrf
<div class="nstk-page">

    {{-- Main form --}}
    <div class="nstk-card">
        <div class="nstk-card-head">
            <div class="nstk-card-icon gold">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="nstk-card-title">Stocktake Details</div>
                <div class="nstk-card-sub">Set up your filters and preferences for this stock count session.</div>
            </div>
        </div>

        <div class="nstk-card-body">
            {{-- Date --}}
            <div>
                <label class="nstk-label" for="stk-date">Stocktake Date <span class="req">*</span></label>
                <input id="stk-date" type="date" name="stocktake_date" class="nstk-input" value="{{ old('stocktake_date', today()->toDateString()) }}" required>
            </div>

            {{-- Category & Vendor --}}
            <div class="nstk-grid-2">
                <div>
                    <label class="nstk-label" for="stk-category">Category Filter</label>
                    <div class="nstk-sel-wrap">
                        <span class="nstk-sel-icon">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        </span>
                        <select id="stk-category" name="category_id" class="nstk-input">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string)old('category_id') === (string)$category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nstk-help">Filter by product categories</div>
                </div>
                <div>
                    <label class="nstk-label" for="stk-vendor">Vendor Filter</label>
                    <div class="nstk-sel-wrap">
                        <span class="nstk-sel-icon">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <select id="stk-vendor" name="vendor_id" class="nstk-input">
                            <option value="">All vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected((string)old('vendor_id') === (string)$vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nstk-help">Filter by product vendors</div>
                </div>
            </div>

            {{-- Product & Stock status --}}
            <div class="nstk-grid-2">
                <div>
                    <label class="nstk-label" for="stk-product">Single Product (Optional)</label>
                    <div class="nstk-sel-wrap">
                        <span class="nstk-sel-icon">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <select id="stk-product" name="product_id" class="nstk-input">
                            <option value="">All matching products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected((string)old('product_id') === (string)$product->id)>{{ $product->name }} ({{ $product->sku ?: 'no SKU' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nstk-help">Focus on a specific product</div>
                </div>
                <div>
                    <label class="nstk-label" for="stk-stock-status">Stock Status Filter</label>
                    <div class="nstk-sel-wrap">
                        <span class="nstk-sel-icon">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </span>
                        <select id="stk-stock-status" name="stock_status" class="nstk-input">
                            <option value="all"  @selected(old('stock_status','all') === 'all')>All products</option>
                            <option value="low"  @selected(old('stock_status') === 'low')>Low stock only</option>
                            <option value="out"  @selected(old('stock_status') === 'out')>Out of stock only</option>
                            <option value="in"   @selected(old('stock_status') === 'in')>In stock only</option>
                        </select>
                    </div>
                    <div class="nstk-help">Filter by current stock status</div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="nstk-label" for="stk-notes">Notes (Optional)</label>
                <div style="position:relative;">
                    <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;top:12px;left:12px;pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <textarea id="stk-notes" name="notes" class="nstk-input" rows="4" style="padding-left:34px;resize:vertical;" maxlength="500" placeholder="Optional notes for this count session">{{ old('notes') }}</textarea>
                </div>
                <div class="nstk-help"><span id="stk-notes-count">0</span> / 500</div>
            </div>

            {{-- Important notice --}}
            <div class="nstk-important">
                <svg class="nstk-important-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <div>
                    <h5>Important</h5>
                    <p>Enter physical counts on the next screen.<br>Stock levels will not change in the system until the stocktake is approved.</p>
                </div>
            </div>
        </div>

        <div class="nstk-actions">
            <a href="{{ route('admin.stock-takes.index') }}" class="nstk-btn-back">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </a>
            <button type="submit" class="nstk-btn-create">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                Create Stocktake
            </button>
        </div>
    </div>

    {{-- Right side panel --}}
    <div class="nstk-panel">
        <div class="nstk-panel-head">
            <h4>What happens next?</h4>
        </div>
        <div class="nstk-steps">
            <div class="nstk-step">
                <div class="nstk-step-icon gold">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h5>1. Create Stocktake</h5>
                    <p>Set your filters and create the stock count session.</p>
                </div>
            </div>
            <div class="nstk-step">
                <div class="nstk-step-icon purple">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <h5>2. Enter Physical Counts</h5>
                    <p>Count items in your store and enter the actual quantities.</p>
                </div>
            </div>
            <div class="nstk-step">
                <div class="nstk-step-icon blue">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                </div>
                <div>
                    <h5>3. Review &amp; Verify</h5>
                    <p>Review the variance report and verify your counts.</p>
                </div>
            </div>
            <div class="nstk-step">
                <div class="nstk-step-icon green">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h5>4. Approve</h5>
                    <p>Approve the stocktake to update inventory levels.</p>
                </div>
            </div>
        </div>
    </div>

</div>
</form>

<script>
const notesEl = document.getElementById('stk-notes');
const notesCount = document.getElementById('stk-notes-count');
notesEl?.addEventListener('input', () => { if(notesCount) notesCount.textContent = notesEl.value.length; });
</script>
@endsection
