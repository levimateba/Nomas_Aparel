@extends('layouts.admin')

@section('title', 'Settings')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Settings</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Business Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Store identity, receipts, tax, branding, and POS behaviour.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="ta-btn-outline">Dashboard</a>
        <button type="submit" form="settings-form" class="ta-btn">Save settings</button>
    </div>
</div>
@endsection

@push('styles')
<style>
.settings-jump {
    position: sticky;
    top: 73px;
    z-index: 45;
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    margin: 0 0 16px;
    padding: 10px 12px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: rgba(255,255,255,.97);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 16px rgba(0,0,0,.06);
}
.settings-jump::-webkit-scrollbar { height: 0; }
.settings-jump a,
.settings-jump button {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
    font-family: inherit;
}
.settings-jump a:hover,
.settings-jump button:hover {
    border-color: #a58112;
    color: #8a6c0f;
    background: #faf7ef;
}
.settings-jump a.is-active,
.settings-jump button.is-active {
    border-color: #a58112;
    background: #a58112;
    color: #fff;
}
.settings-choice {
    display: flex; gap: 12px; align-items: flex-start;
    border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 12px 14px; background: #fff; cursor: pointer;
    transition: border-color .15s, background .15s;
}
.settings-choice:has(input:checked) {
    border-color: #a58112; background: #faf7ef;
}
.settings-choice strong { display: block; font-size: 13px; color: #111827; }
.settings-choice span.desc { display: block; color: #6b7280; font-size: 12px; margin-top: 4px; line-height: 1.4; }
.settings-choice input { margin-top: 2px; accent-color: #a58112; flex-shrink: 0; }
.settings-logo {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    border: 1px dashed #d1d5db; border-radius: 14px; padding: 20px;
    background: #f9fafb; min-height: 160px;
}
.settings-logo img { max-height: 96px; max-width: 100%; object-fit: contain; }
.settings-sticky {
    position: sticky; bottom: 12px; z-index: 40;
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
    margin-top: 8px; padding: 14px 18px;
    border: 1px solid #e5e7eb; border-radius: 16px;
    background: rgba(255,255,255,.96); backdrop-filter: blur(8px);
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
}
#section-business, #section-receipts, #section-drawer, #section-branding,
#section-tax, #section-pos, #section-online {
    scroll-margin-top: 150px;
}
@media (max-width: 640px) {
    .settings-jump { top: 64px; }
}
</style>
@endpush

@section('content')
@php
    $logoPreview = $settings->hasLogoFile()
        ? (\App\Support\PublicStorageUrl::fromPath($settings->logoStoragePath()) ?? $settings->logo)
        : null;
    $locs = $stockLocations ?? collect();
    $mode = old('online_sales_stock_mode', $settings->online_sales_stock_mode ?? 'single');
    $strategy = old('online_fulfilment_strategy', $settings->online_fulfilment_strategy ?? 'priority');
    $selectedIds = old('online_sales_location_ids', $selectedOnlineLocationIds ?? []);
    $priorities = old('online_location_priority', $onlineLocationPriorities ?? []);
@endphp

<div class="ta-page">
    <nav class="settings-jump" id="settings-jump" aria-label="Settings sections">
        <button type="button" data-section="section-business" class="is-active">Business</button>
        <button type="button" data-section="section-receipts">Receipts</button>
        <button type="button" data-section="section-drawer">Cash drawer</button>
        <button type="button" data-section="section-branding">Logo</button>
        <button type="button" data-section="section-tax">Tax &amp; UI</button>
        <button type="button" data-section="section-pos">POS</button>
        <button type="button" data-section="section-online">Online stock</button>
    </nav>

    <form id="settings-form" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
        @csrf

        {{-- Business details --}}
        <div id="section-business" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Business details</h2>
                <p class="mt-0.5 text-sm text-gray-500">Shown on receipts and across the admin panel.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div class="ta-field">
                    <label for="site_name">Application name *</label>
                    <input id="site_name" type="text" name="site_name" class="ta-input" value="{{ old('site_name', $settings->site_name) }}" required>
                </div>
                <div class="ta-field">
                    <label for="trading_name">Trading name</label>
                    <input id="trading_name" type="text" name="trading_name" class="ta-input" value="{{ old('trading_name', $settings->trading_name ?: $settings->site_name) }}">
                </div>
                <div class="ta-field">
                    <label for="business_registration_number">Registration number</label>
                    <input id="business_registration_number" type="text" name="business_registration_number" class="ta-input" value="{{ old('business_registration_number', $settings->business_registration_number) }}">
                </div>
                <div class="ta-field">
                    <label for="tax_pin">Tax PIN</label>
                    <input id="tax_pin" type="text" name="tax_pin" class="ta-input" value="{{ old('tax_pin', $settings->tax_pin) }}">
                </div>
                <div class="ta-field">
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" class="ta-input" value="{{ old('phone', $settings->phone) }}">
                </div>
                <div class="ta-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" class="ta-input" value="{{ old('email', $settings->email) }}">
                </div>
                <div class="ta-field">
                    <label for="city">City</label>
                    <input id="city" type="text" name="city" class="ta-input" value="{{ old('city', $settings->city) }}">
                </div>
                <div class="ta-field">
                    <label for="currency">Currency *</label>
                    <input id="currency" type="text" name="currency" class="ta-input" value="{{ old('currency', $settings->currency ?: 'KES') }}" required>
                </div>
                <div class="ta-field sm:col-span-2">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="2" class="ta-input">{{ old('address', $settings->address) }}</textarea>
                </div>
                <div class="ta-field sm:col-span-2">
                    <label for="receipt_header">Receipt header</label>
                    <textarea id="receipt_header" name="receipt_header" rows="2" class="ta-input">{{ old('receipt_header', $settings->receipt_header) }}</textarea>
                </div>
                <div class="ta-field sm:col-span-2">
                    <label for="receipt_footer">Receipt footer</label>
                    <textarea id="receipt_footer" name="receipt_footer" rows="2" class="ta-input">{{ old('receipt_footer', $settings->receipt_footer) }}</textarea>
                </div>
                <div class="ta-field sm:col-span-2">
                    <label for="site_tagline">Storefront tagline</label>
                    <input id="site_tagline" type="text" name="site_tagline" class="ta-input" value="{{ old('site_tagline', $settings->site_tagline) }}">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label for="footer_text">Website footer text</label>
                    <textarea id="footer_text" name="footer_text" rows="2" class="ta-input">{{ old('footer_text', $settings->footer_text) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Receipt printing --}}
        <div id="section-receipts" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Receipt printing</h2>
                <p class="mt-0.5 text-sm text-gray-500">Choose which receipt copies print after a sale. Two copies never create a second sale.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 p-5 md:grid-cols-3">
                @foreach($receiptPrintModes as $value => $label)
                    <label class="settings-choice">
                        <input type="radio" name="receipt_print_mode" value="{{ $value }}" @checked(old('receipt_print_mode', $settings->receiptPrintMode()) === $value) @if($value === \App\Support\ReceiptPrintMode::CUSTOMER) required @endif>
                        <span>
                            <strong>{{ $label }}</strong>
                            @if($value === \App\Support\ReceiptPrintMode::CUSTOMER)
                                <span class="desc">Prints one customer receipt only.</span>
                            @elseif($value === \App\Support\ReceiptPrintMode::CUSTOMER_AND_BUSINESS)
                                <span class="desc">Prints customer receipt, then business copy.</span>
                            @else
                                <span class="desc">Prints one business copy only.</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Cash drawer --}}
        <div id="section-drawer" class="rounded-2xl border border-success-200 bg-success-50/40 dark:border-success-500/20 dark:bg-success-500/5">
            <div class="border-b border-success-100 px-5 py-4 dark:border-success-500/20">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Cash drawer</h2>
                <p class="mt-0.5 text-sm text-gray-500">Connect the drawer to your receipt printer (RJ11). Press <strong>F12</strong> on POS to test anytime.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 p-5 md:grid-cols-2">
                <label class="settings-choice" style="border-color:#a7f3d0;background:#ecfdf5;">
                    <input type="hidden" name="auto_open_drawer_cash" value="0">
                    <input type="checkbox" name="auto_open_drawer_cash" value="1" @checked(old('auto_open_drawer_cash', $settings->auto_open_drawer_cash ?? true))>
                    <span>
                        <strong>Open drawer on every Cash sale</strong>
                        <span class="desc">Recommended. Also opens when giving change on any payment method.</span>
                    </span>
                </label>
                <label class="settings-choice">
                    <input type="hidden" name="auto_print_receipt" value="0">
                    <input type="checkbox" name="auto_print_receipt" value="1" @checked(old('auto_print_receipt', $settings->auto_print_receipt))>
                    <span>
                        <strong>Auto-print receipt after sale</strong>
                        <span class="desc">Opens the print dialog when a sale completes. Needed for most drawer kicks.</span>
                    </span>
                </label>
                <label class="settings-choice md:col-span-2">
                    <input type="hidden" name="escpos_enabled" value="0">
                    <input type="checkbox" name="escpos_enabled" value="1" @checked(old('escpos_enabled', $settings->escpos_enabled ?? true))>
                    <span>
                        <strong>ESC-POS thermal layout (80mm)</strong>
                        <span class="desc">Use for thermal printers. Leave off for normal A4 / PDF printing.</span>
                    </span>
                </label>
            </div>
        </div>

        {{-- Branding / Logo --}}
        <div id="section-branding" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">System branding / logo</h2>
                <p class="mt-0.5 text-sm text-gray-500">PNG, JPG, or WebP · max 2 MB. Display locations are off by default.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-5 lg:grid-cols-2">
                <div class="settings-logo">
                    @if($logoPreview)
                        <img src="{{ $logoPreview }}?v={{ optional($settings->updated_at)->timestamp }}" alt="System logo">
                        <p class="mt-2 text-xs text-gray-400">Current logo</p>
                    @else
                        <svg class="mb-2 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                        <p class="text-sm text-gray-400">No logo uploaded</p>
                    @endif
                </div>
                <div class="flex flex-col gap-4">
                    <div class="ta-field">
                        <label for="logo">Upload logo</label>
                        <input id="logo" type="file" name="logo" class="ta-input" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                        <p class="text-xs text-gray-400">Recommended: square or wide, at least 200×200 px. Leave empty to keep the current logo.</p>
                    </div>
                    @if($logoPreview)
                        <label class="ta-check">
                            <input type="hidden" name="remove_logo" value="0">
                            <input type="checkbox" name="remove_logo" value="1" @checked(old('remove_logo'))>
                            <span>Remove logo</span>
                        </label>
                    @endif
                </div>
                <div class="lg:col-span-2">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Logo display</p>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <label class="settings-choice">
                            <input type="hidden" name="logo_show_on_login" value="0">
                            <input type="checkbox" name="logo_show_on_login" value="1" @checked(old('logo_show_on_login', $settings->logo_show_on_login))>
                            <span><strong>Login screen</strong><span class="desc">Default: off</span></span>
                        </label>
                        <label class="settings-choice">
                            <input type="hidden" name="logo_show_on_sidebar" value="0">
                            <input type="checkbox" name="logo_show_on_sidebar" value="1" @checked(old('logo_show_on_sidebar', $settings->logo_show_on_sidebar))>
                            <span><strong>Dashboard sidebar</strong><span class="desc">Default: off</span></span>
                        </label>
                        <label class="settings-choice">
                            <input type="hidden" name="logo_show_on_receipts" value="0">
                            <input type="checkbox" name="logo_show_on_receipts" value="1" @checked(old('logo_show_on_receipts', $settings->logo_show_on_receipts))>
                            <span><strong>Receipts</strong><span class="desc">Default: off · thermal-safe. Invoices always show the logo when uploaded.</span></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tax & UI --}}
        <div id="section-tax" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Tax &amp; interface</h2>
                <p class="mt-0.5 text-sm text-gray-500">Default VAT and admin text size.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div class="ta-field">
                    <label for="tax_rate">Default tax rate %</label>
                    <input id="tax_rate" type="number" step="0.01" min="0" max="100" name="tax_rate" class="ta-input" value="{{ old('tax_rate', $settings->tax_rate ?? 16) }}">
                    <p class="text-xs text-gray-400">Used unless a product has its own rate. Kenya VAT is 16%.</p>
                </div>
                <div class="ta-field">
                    <label for="system_font_size">Interface font size</label>
                    <select id="system_font_size" name="system_font_size" class="ta-select" required>
                        @foreach($fontSizes as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('system_font_size', $currentFontSize) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400">Try Large or Extra Large if the UI feels small.</p>
                </div>
                <div class="sm:col-span-2 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label class="settings-choice">
                        <input type="hidden" name="tax_enabled" value="0">
                        <input type="checkbox" name="tax_enabled" value="1" @checked(old('tax_enabled', $settings->tax_enabled))>
                        <span><strong>Tax enabled</strong></span>
                    </label>
                    <label class="settings-choice">
                        <input type="hidden" name="tax_inclusive" value="0">
                        <input type="checkbox" name="tax_inclusive" value="1" @checked(old('tax_inclusive', $settings->tax_inclusive ?? true))>
                        <span><strong>Selling prices include tax (VAT inclusive)</strong></span>
                    </label>
                    <label class="settings-choice">
                        <input type="hidden" name="require_open_shift" value="0">
                        <input type="checkbox" name="require_open_shift" value="1" @checked(old('require_open_shift', $settings->require_open_shift))>
                        <span><strong>Require open shift before sales</strong></span>
                    </label>
                    <label class="settings-choice">
                        <input type="hidden" name="enforce_credit_limit" value="0">
                        <input type="checkbox" name="enforce_credit_limit" value="1" @checked(old('enforce_credit_limit', $settings->enforce_credit_limit ?? true))>
                        <span><strong>Enforce customer credit limits at checkout</strong></span>
                    </label>
                    <label class="settings-choice md:col-span-2">
                        <input type="hidden" name="allow_negative_stock" value="0">
                        <input type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $settings->allow_negative_stock))>
                        <span>
                            <strong>Allow negative stock</strong>
                            <span class="desc">Sell when out of stock. Use carefully for apparel.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        @if(\Illuminate\Support\Facades\Schema::hasTable('stock_locations'))
        {{-- POS location --}}
        <div id="section-pos" class="rounded-2xl border border-warning-200 bg-warning-50/50 dark:border-warning-500/20 dark:bg-warning-500/5">
            <div class="border-b border-warning-100 px-5 py-4 dark:border-warning-500/20">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">POS location (in-store selling)</h2>
                <p class="mt-0.5 text-sm text-gray-600">
                    Choose the primary Shop/counter location for POS. Optionally allow selling stock that still sits in Main Store.
                </p>
            </div>
            <div class="p-5 space-y-4">
                <div class="ta-field max-w-md">
                    <label for="pos_location_id">Current POS location *</label>
                    <select id="pos_location_id" name="pos_location_id" class="ta-select" required>
                        @foreach($locs as $loc)
                            <option value="{{ $loc->id }}" @selected((int)old('pos_location_id', $settings->pos_location_id) === (int)$loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-amber-800">
                        Primary counter location. Sales deduct here first when multi-location selling is on.
                    </p>
                </div>
                @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'pos_sell_from_all_locations'))
                <label class="settings-choice">
                    <input type="hidden" name="pos_sell_from_all_locations" value="0">
                    <input type="checkbox" name="pos_sell_from_all_locations" value="1" @checked(old('pos_sell_from_all_locations', $settings->pos_sell_from_all_locations ?? false))>
                    <span>
                        <strong>Allow POS to sell from all locations</strong>
                        <span class="desc">If stock is only in Main Store (not on Shop display), POS can still sell it. Deducts Shop first, then other locations.</span>
                    </span>
                </label>
                @endif
            </div>
        </div>

        {{-- Online inventory --}}
        <div id="section-online" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Online sales inventory (website only)</h2>
                <p class="mt-0.5 text-sm text-gray-500">
                    Controls <strong>website / online orders only</strong> — not the POS counter.
                    “All locations” does <strong>not</strong> let POS sell stock that is still in Main Store.
                </p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <label class="settings-choice">
                        <input type="radio" name="online_sales_stock_mode" value="single" @checked($mode === 'single')>
                        <span><strong>Single location</strong><span class="desc">One warehouse / store for online.</span></span>
                    </label>
                    <label class="settings-choice">
                        <input type="radio" name="online_sales_stock_mode" value="selected" @checked($mode === 'selected')>
                        <span><strong>Selected locations</strong><span class="desc">Pick which locations can fulfil.</span></span>
                    </label>
                    <label class="settings-choice">
                        <input type="radio" name="online_sales_stock_mode" value="all" @checked($mode === 'all')>
                        <span><strong>All locations</strong><span class="desc">Use every active location.</span></span>
                    </label>
                </div>

                <div id="online-single-wrap" style="{{ $mode === 'single' ? '' : 'display:none;' }}">
                    <div class="ta-field max-w-md">
                        <label for="online_sales_location_id">Online inventory location</label>
                        <select id="online_sales_location_id" name="online_sales_location_id" class="ta-select">
                            @foreach($locs as $loc)
                                <option value="{{ $loc->id }}" @selected((int)old('online_sales_location_id', $settings->online_sales_location_id) === (int)$loc->id)>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="online-selected-wrap" style="{{ $mode === 'selected' ? '' : 'display:none;' }}">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Locations</p>
                    <div class="flex flex-col gap-2">
                        @foreach($locs as $loc)
                            <label class="settings-choice">
                                <input type="checkbox" class="online-loc-check" name="online_sales_location_ids[]" value="{{ $loc->id }}" @checked(in_array((int)$loc->id, array_map('intval', (array)$selectedIds), true))>
                                <span style="flex:1;">
                                    <strong>{{ $loc->name }}</strong>
                                    <span class="desc">{{ strtoupper($loc->code) }} · {{ $loc->type }}</span>
                                </span>
                                <span class="online-priority-field inline-flex items-center gap-2">
                                    <span class="text-xs text-gray-400">Priority</span>
                                    <input type="number" min="1" name="online_location_priority[{{ $loc->id }}]" class="ta-input" style="width:72px;" value="{{ $priorities[$loc->id] ?? '' }}" placeholder="#">
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-400">Lower priority number is fulfilled first when strategy is Priority order.</p>
                </div>

                <div id="online-strategy-wrap" style="{{ in_array($mode, ['selected','all'], true) ? '' : 'display:none;' }}">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Fulfilment strategy</p>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <label class="settings-choice">
                            <input type="radio" name="online_fulfilment_strategy" value="priority" @checked($strategy === 'priority')>
                            <span><strong>Priority order</strong><span class="desc">Consume from highest-priority location first, then split if needed.</span></span>
                        </label>
                        <label class="settings-choice">
                            <input type="radio" name="online_fulfilment_strategy" value="manual" @checked($strategy === 'manual')>
                            <span><strong>Select when processing</strong><span class="desc">Checkout reserves stock safely until a manual allocation is used.</span></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="settings-sticky">
            <p class="text-sm text-gray-500">Changes apply immediately after save.</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.dashboard') }}" class="ta-btn-outline">Cancel</a>
                <button type="submit" class="ta-btn">Save settings</button>
            </div>
        </div>
    </form>
</div>

@if(\Illuminate\Support\Facades\Schema::hasTable('stock_locations'))
<script>
function syncOnlineStockSettingsUi() {
    const mode = document.querySelector('input[name="online_sales_stock_mode"]:checked')?.value || 'single';
    const single = document.getElementById('online-single-wrap');
    const selected = document.getElementById('online-selected-wrap');
    const strategyWrap = document.getElementById('online-strategy-wrap');
    if (single) single.style.display = mode === 'single' ? '' : 'none';
    if (selected) selected.style.display = mode === 'selected' ? '' : 'none';
    if (strategyWrap) strategyWrap.style.display = (mode === 'selected' || mode === 'all') ? '' : 'none';
    const strategy = document.querySelector('input[name="online_fulfilment_strategy"]:checked')?.value || 'priority';
    document.querySelectorAll('.online-priority-field').forEach(el => {
        el.style.visibility = (mode === 'selected' && strategy === 'priority') ? 'visible' : 'hidden';
    });
}
document.querySelectorAll('input[name="online_sales_stock_mode"], input[name="online_fulfilment_strategy"]').forEach(el => {
    el.addEventListener('change', syncOnlineStockSettingsUi);
});
syncOnlineStockSettingsUi();
</script>
@endif

<script>
(function () {
    const nav = document.getElementById('settings-jump');
    if (!nav) return;
    const buttons = Array.from(nav.querySelectorAll('[data-section]'));
    const sections = buttons
        .map(btn => document.getElementById(btn.dataset.section))
        .filter(Boolean);

    function stickyOffset() {
        const header = document.querySelector('header.sticky');
        const headerH = header ? header.getBoundingClientRect().height : 72;
        const navH = nav.getBoundingClientRect().height || 52;
        return headerH + navH + 16;
    }

    function setActive(id) {
        buttons.forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.section === id);
        });
    }

    function scrollToSection(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const top = el.getBoundingClientRect().top + window.pageYOffset - stickyOffset();
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        setActive(id);
        history.replaceState(null, '', '#' + id);
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            scrollToSection(btn.dataset.section);
        });
    });

    // Keep active tab in sync while scrolling
    let ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            const offset = stickyOffset() + 8;
            let current = sections[0]?.id;
            for (const section of sections) {
                if (section.getBoundingClientRect().top - offset <= 0) {
                    current = section.id;
                }
            }
            if (current) setActive(current);
            ticking = false;
        });
    }, { passive: true });

    // Open hash on load (e.g. #section-receipts)
    if (location.hash) {
        const id = location.hash.replace(/^#/, '');
        if (document.getElementById(id)) {
            setTimeout(() => scrollToSection(id), 50);
        }
    }
})();
</script>
@endsection
