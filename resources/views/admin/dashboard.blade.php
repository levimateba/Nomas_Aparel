@extends('layouts.admin')
@section('title', 'Dashboard')

@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $userName = auth()->user()?->name ?? 'Admin';
    $siteName = $settings->displayName() ?: ($settings->site_name ?? 'Nomas Apparel');
    $tagline = $settings->site_tagline ?: 'Style for Everyone';
    $catTotal = collect($categoryBreakdown ?? [])->sum('count');
    $inactiveCount = (int) ($inactiveProductCount ?? max(0, $productCount - $activeProductCount));
    $notifyTotal = (int) (($pendingOrderCount ?? 0) + ($lowStockCount ?? 0) + ($enquiryCount ?? 0));
    $trendPill = function (?float $value, string $positive = 'text-success-700 bg-success-50', string $negative = 'text-error-700 bg-error-50') {
        if ($value === null) {
            return ['—', 'text-gray-500 bg-gray-100'];
        }
        $label = ($value >= 0 ? '+' : '').number_format($value, 0).'%';
        $class = $value >= 0 ? $positive : $negative;

        return [$label, $class];
    };
@endphp

@section('content')
<div class="space-y-5 pb-24 xl:pb-0">
    {{-- Welcome banner (mobile mockup) --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#f7f1e3] via-[#faf7f0] to-white p-4 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 sm:p-5">
        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-brand-600">{{ $greeting }}, {{ $userName }} 👋</p>
                <h1 class="mt-1 text-2xl font-bold leading-snug text-gray-800 dark:text-white/90 sm:text-3xl">
                    Welcome back!
                </h1>
                <p class="mt-1.5 max-w-xl text-sm text-gray-500 dark:text-gray-400">Here's what's happening in your business today.</p>
            </div>
            <div class="pointer-events-none w-[4.5rem] shrink-0 sm:w-28" aria-hidden="true">
                <div class="relative mx-auto flex h-[4.5rem] w-[4.5rem] items-center justify-center sm:h-24 sm:w-24">
                    <svg class="h-full w-full text-brand-600" viewBox="0 0 96 96" fill="none">
                        <ellipse cx="48" cy="86" rx="28" ry="4" fill="currentColor" opacity=".12"/>
                        <path d="M30 34h36l4 42H26l4-42z" fill="#c4a574" stroke="#8b6914" stroke-width="1.5"/>
                        <path d="M34 34c0-10 6-18 14-18s14 8 14 18" stroke="#8b6914" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                        <rect x="38" y="48" width="20" height="14" rx="2" fill="#f6f0df" stroke="#8b6914" stroke-width="1"/>
                        <text x="48" y="58" text-anchor="middle" font-size="5" font-weight="700" fill="#8b6914">NOMAS</text>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Date + New Sale (desktop / tablet) --}}
    <div class="hidden gap-3 sm:flex sm:flex-row sm:items-stretch">
        <div class="flex flex-1 items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </span>
            <div>
                <div class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ now()->format('l, d M Y') }}</div>
                <div class="text-xs text-gray-500">{{ now()->format('h:i A') }}</div>
            </div>
        </div>
        @if(auth()->user()?->hasPermission('create_sale'))
            <a href="{{ route('admin.pos.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3.5 text-sm font-bold text-gray-900 shadow-theme-xs transition hover:bg-brand-600 sm:min-w-[160px]">
                <span class="text-lg leading-none">+</span>
                New Sale
                <span aria-hidden="true">›</span>
            </a>
        @endif
    </div>

    {{-- KPI grid (tablet+) --}}
    <div class="hidden grid-cols-2 gap-3 sm:grid lg:gap-4 xl:grid-cols-4">
        @php [$prodTrendLabel, $prodTrendClass] = $trendPill($trends['products'] ?? null); @endphp
        <a href="{{ route('admin.products.index') }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 sm:h-11 sm:w-11">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $prodTrendClass }}">{{ $prodTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Products</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($productCount) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">{{ $activeProductCount }} active · {{ $inactiveCount }} inactive</p>
            <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($productSparkline ?? [])' data-color="#7A5AF8"></div>
        </a>

        @php [$lowTrendLabel, $lowTrendClass] = $trendPill($trends['lowStock'] ?? 0, 'text-success-700 bg-success-50', 'text-error-700 bg-error-50'); @endphp
        <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-error-50 text-error-600 sm:h-11 sm:w-11">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $lowStockCount > 0 ? 'text-error-700 bg-error-50' : 'text-success-700 bg-success-50' }}">
                    {{ $lowStockCount > 0 ? 'Alert' : $lowTrendLabel }}
                </span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Low Stock</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($lowStockCount) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Products below threshold</p>
            <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($lowStockSparkline ?? $pendingSparkline ?? [])' data-color="#F04438"></div>
        </a>

        @php [$salesTrendLabel, $salesTrendClass] = $trendPill($trends['sales'] ?? null); @endphp
        <a href="{{ route('admin.orders.index') }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-success-50 text-success-600 sm:h-11 sm:w-11">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 22a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $salesTrendClass }}">{{ $salesTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Sales</p>
            <p class="mt-0.5 text-lg font-bold text-gray-800 dark:text-white/90 sm:text-2xl">KES {{ number_format($todaySales ?? 0, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Today's sales</p>
            <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($salesSparkline ?? $revenueSparkline ?? [])' data-color="#12B76A"></div>
        </a>

        <a href="{{ route('admin.customers.index') }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-light-50 text-blue-light-600 sm:h-11 sm:w-11">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-8a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="rounded-full bg-blue-light-50 px-2 py-0.5 text-[10px] font-bold text-blue-light-700">Live</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Customers</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($shopCustomerCount ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Registered customers</p>
            <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($customerSparkline ?? [])' data-color="#465FFF"></div>
        </a>
    </div>

    {{-- Quick Actions + side widgets --}}
    <div class="dash-qa-layout">
        <div class="min-w-0">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Quick Actions</h2>
                <a href="{{ route('admin.cashier.home') }}" class="text-sm font-semibold text-brand-600 hover:underline">See All →</a>
            </div>

            <div class="dash-qa-grid">
                @if(auth()->user()?->hasPermission('create_sale'))
                <a href="{{ route('admin.pos.index') }}" class="dash-qa-tile dash-qa-tile--sale">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">New Sale</p>
                    <p class="dash-qa-tile__desc">Create a new POS sale</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('manage_products'))
                <a href="{{ route('admin.products.index') }}" class="dash-qa-tile dash-qa-tile--products">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Products</p>
                    <p class="dash-qa-tile__desc">Manage products &amp; stock</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('manage_customers'))
                <a href="{{ route('admin.customers.index') }}" class="dash-qa-tile dash-qa-tile--customers">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-8a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Customers</p>
                    <p class="dash-qa-tile__desc">View &amp; manage customers</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('view_pos_reports'))
                <a href="{{ route('admin.reports.index') }}" class="dash-qa-tile dash-qa-tile--reports">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Reports</p>
                    <p class="dash-qa-tile__desc">View business reports</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('create_stock_transfers') || auth()->user()?->hasPermission('manage_products'))
                <a href="{{ route('admin.stock-transfers.create') }}" class="dash-qa-tile dash-qa-tile--transfer">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Stock Transfer</p>
                    <p class="dash-qa-tile__desc">Move stock between locations</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('adjust_stock') || auth()->user()?->hasPermission('manage_products'))
                <a href="{{ route('admin.stock-adjustments.create') }}" class="dash-qa-tile dash-qa-tile--adjust">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.543.94.94 3.31-.94 2.37a1.724 1.724 0 00-2.572 1.065c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572-1.065c-.94 1.543-3.31.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.543-.94-.94-3.31.94-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Stock Adjustment</p>
                    <p class="dash-qa-tile__desc">Adjust inventory levels</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('manage_purchase_orders') || auth()->user()?->hasPermission('manage_purchases') || auth()->user()?->hasPermission('manage_products'))
                <a href="{{ route('admin.purchase-orders.index') }}" class="dash-qa-tile dash-qa-tile--po dash-qa-tile--desktop-only">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Purchase Orders</p>
                    <p class="dash-qa-tile__desc">Supplier orders</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif

                @if(auth()->user()?->hasPermission('view_sales') || auth()->user()?->hasPermission('create_sale'))
                <a href="{{ route('admin.returns.index') }}" class="dash-qa-tile dash-qa-tile--returns dash-qa-tile--desktop-only">
                    <span class="dash-qa-tile__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </span>
                    <p class="dash-qa-tile__title">Returns</p>
                    <p class="dash-qa-tile__desc">Process returns</p>
                    <span class="dash-qa-tile__arrow" aria-hidden="true">→</span>
                </a>
                @endif
            </div>

            @if($notifyTotal > 0)
            <button type="button" onclick="document.querySelector('[data-notify-btn]')?.click()" class="dash-notify-banner w-full border-0 text-left">
                <span class="dash-notify-banner__icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-gray-800 dark:text-white/90">You have {{ $notifyTotal }} new notification{{ $notifyTotal === 1 ? '' : 's' }}</span>
                    <span class="mt-0.5 block text-xs text-gray-500">Check for important updates, stock alerts and messages.</span>
                </span>
                <span class="dash-notify-banner__arrow" aria-hidden="true">→</span>
            </button>
            @endif
        </div>

        <div class="hidden min-w-0 space-y-4 xl:block">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Recent Activity</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View all</a>
                </div>
                <ul class="space-y-3">
                    @forelse(($recentOrders ?? [])->take(4) as $order)
                        <li class="flex items-start gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ in_array($order->status, ['paid','delivered','completed'], true) ? 'bg-success-500' : 'bg-brand-500' }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">Sale {{ $order->order_number }}</p>
                                <p class="text-xs text-gray-400">{{ $order->customer_name ?: 'Guest' }} · KES {{ number_format((float) $order->total_amount, 0) }}</p>
                            </div>
                            <span class="shrink-0 text-[10px] text-gray-400">{{ $order->created_at?->format('h:i A') }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-gray-400">No recent activity yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Low Stock Items</h3>
                    <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs font-semibold text-brand-600 hover:underline">View all</a>
                </div>
                @if(($lowStockProducts ?? collect())->isEmpty())
                    <div class="flex flex-col items-center py-4 text-center">
                        <span class="mb-2 flex h-14 w-14 items-center justify-center rounded-2xl bg-success-50 text-success-600">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Great! No low stock items.</p>
                        <p class="mt-1 text-xs text-gray-400">Your stock levels look healthy.</p>
                    </div>
                @else
                    <ul class="space-y-2.5">
                        @foreach(($lowStockProducts ?? [])->take(4) as $product)
                            <li class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->sku }}</p>
                                </div>
                                <span class="rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-bold text-error-700">{{ $product->stock }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Desktop charts + tables (hidden on small phones as secondary) --}}
    <div class="hidden space-y-5 sm:block">
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Sales Overview</h3>
                        <p class="text-sm text-gray-500">Last 14 days revenue</p>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">Reports</a>
                </div>
                <div id="dash-sales-chart"></div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500">Total Sales</p>
                        <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white/90">KES {{ number_format($salesSummary['total_sales'] ?? 0, 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500">Total Orders</p>
                        <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white/90">{{ number_format($salesSummary['total_orders'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500">Customers</p>
                        <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white/90">{{ number_format($salesSummary['customers'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500">Avg. Order</p>
                        <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white/90">KES {{ number_format($salesSummary['avg_order'] ?? 0, 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-3">
                <div class="mb-3">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Product Categories</h3>
                    <p class="text-sm text-gray-500">Active catalogue mix</p>
                </div>
                <div id="dash-category-chart"></div>
                <ul class="mt-3 space-y-2">
                    @forelse($categoryBreakdown ?? [] as $i => $cat)
                        @php
                            $colors = ['#A58112','#465FFF','#12B76A','#F79009','#EE46BC','#7A5AF8'];
                            $pct = $catTotal > 0 ? round(($cat['count'] / $catTotal) * 100) : 0;
                        @endphp
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <span class="h-2.5 w-2.5 rounded-full" style="background:{{ $colors[$i % count($colors)] }}"></span>
                                {{ $cat['name'] }}
                            </span>
                            <span class="font-semibold text-gray-800 dark:text-white/90">{{ $cat['count'] }} · {{ $pct }}%</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">No categorized products yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-3">
                <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">More actions</h3>
                <div class="space-y-2.5">
                    @if(auth()->user()?->hasPermission('manage_products'))
                    <a href="{{ route('admin.products.create') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                        <span>Add Product</span><span>›</span>
                    </a>
                    @endif
                    @if(auth()->user()?->hasPermission('manage_stocktakes'))
                    <a href="{{ route('admin.stock-takes.create') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                        <span>Manage Stock</span><span>›</span>
                    </a>
                    @endif
                    @if(auth()->user()?->hasPermission('view_sales'))
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                        <span>Sales History</span><span>›</span>
                    </a>
                    @endif
                    @if(auth()->user()?->hasPermission('manage_employees'))
                    <a href="{{ route('admin.employees.index') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                        <span>Employees</span><span>›</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-7">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent Orders</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Order ID</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Customer</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recentOrders as $order)
                            @php
                                $pill = match($order->status) {
                                    'paid', 'delivered', 'completed' => 'bg-success-50 text-success-700',
                                    'processing', 'shipped' => 'bg-blue-light-50 text-blue-light-700',
                                    'cancelled' => 'bg-error-50 text-error-700',
                                    default => 'bg-brand-50 text-brand-700',
                                };
                            @endphp
                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                <td class="px-5 py-3.5 font-semibold text-gray-800 dark:text-white/90">{{ $order->order_number }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $order->customer_name ?: '—' }}</td>
                                <td class="px-5 py-3.5 font-semibold text-gray-800 dark:text-white/90">KES {{ number_format((float) $order->total_amount, 2) }}</td>
                                <td class="px-5 py-3.5"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pill }}">{{ ucwords(str_replace('_',' ', $order->status)) }}</span></td>
                                <td class="px-5 py-3.5 text-gray-500">{{ $order->created_at?->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No recent orders.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-5">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Low Stock Alert</h3>
                    <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-sm font-semibold text-brand-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($lowStockProducts as $product)
                        @php $qty = (int) $product->stock; @endphp
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3 px-5 py-3.5 no-underline hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 text-xs font-bold text-gray-500">
                                @if($product->image_url ?? $product->image ?? null)
                                    <img src="{{ $product->image_url ?? $product->image }}" alt="" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</p>
                                <p class="truncate text-xs text-gray-400">{{ $product->sku ?: 'No SKU' }} · {{ $product->category?->name ?: 'Uncategorized' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold {{ $qty <= 0 ? 'text-error-600' : 'text-warning-600' }}">{{ $qty }}</p>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $qty <= 0 ? 'bg-error-50 text-error-700' : 'bg-warning-50 text-warning-700' }}">
                                    {{ $qty <= 0 ? 'Out of Stock' : 'Low Stock' }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-success-600">All stocked up 🎉</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.dashSalesChart = @json($salesChart ?? ['labels' => [], 'revenue' => [], 'orders' => []]);
    window.dashCategoryChart = @json($categoryBreakdown ?? []);
</script>
@endpush
@endsection
