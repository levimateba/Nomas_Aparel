@extends('layouts.admin')
@section('title', 'Cashier Home')

@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $userName = auth()->user()?->name ?? 'Cashier';
    $firstName = explode(' ', trim($userName))[0] ?: 'Cashier';
    $money = fn ($n) => 'KES '.number_format((float) $n, 0);
    $money2 = fn ($n) => 'KES '.number_format((float) $n, 2);
    $printHref = $lastReceipt
        ? route('admin.pos.receipt', $lastReceipt)
        : route('admin.orders.index');
    $productsHref = auth()->user()?->hasPermission('manage_products')
        ? route('admin.products.index')
        : route('admin.pos.index');
    $discountHref = auth()->user()?->hasPermission('manage_coupons')
        ? route('admin.coupons.index')
        : route('admin.pos.index');
    $settingsHref = auth()->user()?->hasPermission('manage_system_settings')
        ? route('admin.settings.edit')
        : route('admin.profile.edit');
    $shiftHref = route('admin.shifts.index');
    $trendPill = function (?float $value) {
        if ($value === null) {
            return ['0%', 'text-gray-500 bg-gray-100'];
        }
        $label = ($value >= 0 ? '↑ ' : '↓ ').number_format(abs($value), 0).'%';
        $class = $value >= 0 ? 'text-success-700 bg-success-50' : 'text-error-700 bg-error-50';

        return [$label, $class];
    };
    $paymentPill = function (?string $method): array {
        $label = ucwords(str_replace('_', ' ', (string) $method)) ?: '—';
        $key = strtolower((string) $method);
        $class = match (true) {
            str_contains($key, 'cash') => 'bg-success-50 text-success-700',
            str_contains($key, 'mpesa') || str_contains($key, 'mobile') => 'bg-blue-light-50 text-blue-light-700',
            str_contains($key, 'card') => 'bg-purple-50 text-purple-700',
            default => 'bg-gray-100 text-gray-600',
        };

        return [$label, $class];
    };
@endphp

@section('content')
<div class="space-y-5 pb-24 xl:pb-0">

    {{-- ===================== DESKTOP ===================== --}}
    <div class="hidden space-y-5 lg:block">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90 lg:text-3xl">
                    {{ $greeting }}, {{ $firstName }} 👋
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ready to make sales today? Everything you need is right here.</p>
                @if($openShift)
                    <p class="mt-1 text-xs font-semibold text-success-600">
                        Shift open since {{ $openShift->opened_at?->format('h:i A') }}
                        @if(!empty($shiftSummary['net_sales']))
                            · {{ $money($shiftSummary['net_sales'] ?? 0) }}
                        @endif
                    </p>
                @endif
            </div>
            <div class="flex shrink-0 items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ now()->format('l, d M Y') }}</p>
                    <p class="text-xs text-gray-500">{{ now()->format('h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="grid grid-cols-4 gap-4">
            @php [$tLabel, $tClass] = $trendPill($trends['my_sales'] ?? 0); @endphp
            <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-2">
                    <div class="ta-kpi-icon is-blue !h-11 !w-11">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tClass }}">{{ $tLabel }}</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">My Sales Today</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($todayCount) }}</p>
                <p class="mt-1 text-[11px] text-gray-400">Transactions</p>
                <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($sparklines['my_sales'] ?? [])' data-color="#465FFF"></div>
            </a>

            @php [$tLabel, $tClass] = $trendPill($trends['my_revenue'] ?? 0); @endphp
            <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-2">
                    <div class="ta-kpi-icon is-success !h-11 !w-11">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tClass }}">{{ $tLabel }}</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">My Revenue Today</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $money($todayRevenue) }}</p>
                <p class="mt-1 text-[11px] text-gray-400">Total sales</p>
                <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($sparklines['my_revenue'] ?? [])' data-color="#12B76A"></div>
            </a>

            @php [$tLabel, $tClass] = $trendPill($trends['store_sales'] ?? 0); @endphp
            <a href="{{ route('admin.orders.index') }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-2">
                    <div class="ta-kpi-icon is-brand !h-11 !w-11">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tClass }}">{{ $tLabel }}</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Store Sales Today</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($storeTodayCount) }}</p>
                <p class="mt-1 text-[11px] text-gray-400">Total transactions</p>
                <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($sparklines['store_sales'] ?? [])' data-color="#A58112"></div>
            </a>

            @php [$tLabel, $tClass] = $trendPill($trends['store_revenue'] ?? 0); @endphp
            <a href="{{ route('admin.orders.index') }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-2">
                    <div class="ta-kpi-icon is-purple !h-11 !w-11">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tClass }}">{{ $tLabel }}</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Store Revenue Today</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $money($storeTodayRevenue) }}</p>
                <p class="mt-1 text-[11px] text-gray-400">Total sales</p>
                <div class="mt-2 h-10 overflow-hidden" data-sparkline='@json($sparklines['store_revenue'] ?? [])' data-color="#7A5AF8"></div>
            </a>
        </div>

        {{-- Quick Actions 8 tiles --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Quick Actions</h2>
            </div>
            <div class="cashier-qa-grid">
                <a href="{{ route('admin.pos.index') }}" class="cashier-qa cashier-qa--pos">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M8 11h.01M12 11h.01M16 11h.01M8 15h8"/></svg>
                    </span>
                    <span class="cashier-qa__label">POS Terminal</span>
                    <span class="cashier-qa__sub">F2</span>
                </a>
                <a href="{{ route('admin.pos.scan') }}" class="cashier-qa cashier-qa--scan">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M20 7V5a1 1 0 00-1-1h-2M20 17v2a1 1 0 01-1 1h-2M7 12h10"/></svg>
                    </span>
                    <span class="cashier-qa__label">Scan &amp; Sell</span>
                </a>
                <a href="{{ $shiftHref }}" class="cashier-qa cashier-qa--shift">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="cashier-qa__label">{{ $openShift ? 'Manage Shift' : 'Open Shift' }}</span>
                </a>
                @if(auth()->user()?->hasPermission('manage_products'))
                <a href="{{ $productsHref }}" class="cashier-qa cashier-qa--products">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="cashier-qa__label">View Products</span>
                </a>
                @endif
                @if(auth()->user()?->hasPermission('manage_coupons'))
                <a href="{{ $discountHref }}" class="cashier-qa cashier-qa--discount">
                    <span class="cashier-qa__icon"><span class="text-lg font-bold">%</span></span>
                    <span class="cashier-qa__label">Apply Discount</span>
                </a>
                @endif
                <a href="{{ route('admin.orders.index') }}" class="cashier-qa cashier-qa--history">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="cashier-qa__label">Sales History</span>
                </a>
                <a href="{{ $printHref }}" class="cashier-qa cashier-qa--receipt">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </span>
                    <span class="cashier-qa__label">Print Receipt</span>
                </a>
                @if(auth()->user()?->hasPermission('manage_customers'))
                <a href="{{ route('admin.customers.index') }}" class="cashier-qa cashier-qa--products">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <span class="cashier-qa__label">Customers</span>
                </a>
                @endif
                <a href="{{ $settingsHref }}" class="cashier-qa cashier-qa--settings">
                    <span class="cashier-qa__icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.543.94.94 3.31-.94 2.37a1.724 1.724 0 00-2.572 1.065c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572-1.065c-.94 1.543-3.31.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.543-.94-.94-3.31.94-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span class="cashier-qa__label">{{ auth()->user()?->hasPermission('manage_system_settings') ? 'POS Settings' : 'My Profile' }}</span>
                </a>
            </div>
        </div>

        {{-- Recent sales + payment chart --}}
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-7 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent POS Sales</h2>
                    <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="text-sm font-semibold text-brand-600 hover:underline">View All →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">#</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Receipt</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Items</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Payment</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Time</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recentOrders as $i => $order)
                            @php [$payLabel, $payClass] = $paymentPill($order->payment_method); @endphp
                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800" x-data="{ open: false }">
                                <td class="px-5 py-3.5 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-3.5 font-semibold text-gray-800 dark:text-white/90">#{{ $order->order_number }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ (int) ($order->items_count ?? 0) }}</td>
                                <td class="px-5 py-3.5 font-semibold text-gray-800 dark:text-white/90">{{ $money($order->total_amount) }}</td>
                                <td class="px-5 py-3.5"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $payClass }}">{{ $payLabel }}</span></td>
                                <td class="px-5 py-3.5 text-gray-500">{{ $order->created_at?->format('h:i A') }}</td>
                                <td class="relative px-5 py-3.5 text-right">
                                    <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-50 hover:text-gray-700" aria-label="Actions">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute right-5 z-20 mt-1 w-40 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 text-left shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
                                        <a href="{{ route('admin.pos.receipt', $order) }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200">Print receipt</a>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200">View order</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No POS sales yet today.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 bg-[#fbf6ea] px-5 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-brand-500/10 dark:text-gray-300">
                    @if($recentOrders->isEmpty())
                        No more sales today. <a href="{{ route('admin.pos.index') }}" class="font-semibold text-brand-700 hover:underline">Start a new sale</a> to see transactions here.
                    @else
                        Showing today’s POS sales. <a href="{{ route('admin.pos.index') }}" class="font-semibold text-brand-700 hover:underline">Start a new sale</a>
                    @endif
                </div>
            </div>

            <div class="col-span-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Today’s Sales Summary</h2>
                <p class="mt-0.5 text-sm text-gray-500">Your POS revenue by payment method</p>
                <div id="cashier-pay-chart" class="mx-auto mt-2"></div>
                <ul class="mt-2 space-y-2.5">
                    @foreach(($paymentChart['labels'] ?? []) as $i => $label)
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                <span class="h-2.5 w-2.5 rounded-full" style="background:{{ $paymentChart['colors'][$i] ?? '#9ca3af' }}"></span>
                                {{ $label }}
                            </span>
                            <span class="font-semibold text-gray-800 dark:text-white/90">
                                {{ $money($paymentChart['series'][$i] ?? 0) }}
                                <span class="ml-1 text-xs font-medium text-gray-400">{{ (int) ($paymentChart['percents'][$i] ?? 0) }}%</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Extra tools --}}
        <div class="grid grid-cols-4 gap-3">
            <a href="{{ route('admin.holds.index') }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-3.5 no-underline hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm font-bold text-gray-800 dark:text-white/90">Hold Sales</p>
                <p class="mt-0.5 text-xs text-gray-400">Resume parked carts</p>
            </a>
            @if(auth()->user()?->hasPermission('process_return'))
            <a href="{{ route('admin.returns.index') }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-3.5 no-underline hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm font-bold text-gray-800 dark:text-white/90">Returns</p>
                <p class="mt-0.5 text-xs text-gray-400">Process refunds</p>
            </a>
            @endif
            <a href="{{ route('admin.training') }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-3.5 no-underline hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm font-bold text-gray-800 dark:text-white/90">Training</p>
                <p class="mt-0.5 text-xs text-gray-400">Cashier guide</p>
            </a>
            @if(auth()->user()?->hasPermission('manage_customers'))
            <a href="{{ route('admin.customers.index') }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-3.5 no-underline hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm font-bold text-gray-800 dark:text-white/90">Customers</p>
                <p class="mt-0.5 text-xs text-gray-400">Shop customers</p>
            </a>
            @endif
        </div>

        {{-- Bottom CTA --}}
        <div class="cashier-cta flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-lg font-bold text-white">Start a New Sale</p>
                <p class="mt-0.5 text-sm text-white/80">Quick, easy and reliable checkout</p>
            </div>
            <a href="{{ route('admin.pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-gray-900 no-underline shadow-sm hover:bg-gray-50">
                Open POS Terminal →
            </a>
        </div>
    </div>

    {{-- ===================== MOBILE ===================== --}}
    <div class="space-y-5 lg:hidden">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90">
                    {{ $greeting }}, {{ $firstName }} 👋
                </h1>
                <p class="mt-1 text-sm text-gray-500">Ready to make sales today?</p>
            </div>
            <div class="flex shrink-0 items-center gap-2 rounded-2xl bg-[#f6f0df] px-3 py-2.5 dark:bg-brand-500/15">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-brand-700 shadow-sm dark:bg-white/10">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
                <div>
                    <p class="text-xs font-bold text-gray-800 dark:text-white/90">{{ now()->format('D, d M Y') }}</p>
                    <p class="text-[11px] text-gray-500">{{ now()->format('h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Sell actions first — visible without scrolling --}}
        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ route('admin.pos.scan') }}" class="cashier-hero cashier-hero--scan" style="padding:0.875rem;">
                <div class="cashier-hero__row" style="gap:0.5rem;">
                    <span class="cashier-hero__icon" style="height:2.75rem;width:2.75rem;">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M20 7V5a1 1 0 00-1-1h-2M20 17v2a1 1 0 01-1 1h-2M7 12h10"/></svg>
                    </span>
                    <div class="cashier-hero__body">
                        <p class="cashier-hero__title" style="font-size:1rem;">Scan &amp; Sell</p>
                        <p class="cashier-hero__muted" style="font-size:0.75rem;">Tap to scan</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.pos.index') }}" class="cashier-hero cashier-hero--pos" style="padding:0.875rem;">
                <div class="cashier-hero__row" style="gap:0.5rem;">
                    <span class="cashier-hero__icon" style="height:2.75rem;width:2.75rem;">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M8 11h.01M12 11h.01M16 11h.01M8 15h8"/></svg>
                    </span>
                    <div class="cashier-hero__body">
                        <p class="cashier-hero__title" style="font-size:1rem;">New Sale</p>
                        <p class="cashier-hero__muted" style="font-size:0.75rem;">POS · F2</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="group flex flex-col rounded-2xl bg-[#e8f0fe] p-3.5 no-underline dark:bg-blue-light-500/10">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-blue-light-600 shadow-sm"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg></span>
                    <span class="text-blue-light-600">›</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase text-blue-light-700/80">My Sales Today</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($todayCount) }}</p>
                <p class="mt-0.5 text-[11px] text-gray-500">Transactions</p>
            </a>
            <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="group flex flex-col rounded-2xl bg-[#e7f8ef] p-3.5 no-underline dark:bg-success-500/10">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-success-600 shadow-sm"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <span class="text-success-600">›</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase text-success-700/80">My Revenue Today</p>
                <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90">{{ $money($todayRevenue) }}</p>
                <p class="mt-0.5 text-[11px] text-gray-500">Total sales amount</p>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="group flex flex-col rounded-2xl bg-[#f6f0df] p-3.5 no-underline dark:bg-brand-500/10">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-700 shadow-sm"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
                    <span class="text-brand-600">›</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase text-brand-800/80">Store Sales Today</p>
                <p class="mt-0.5 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($storeTodayCount) }}</p>
                <p class="mt-0.5 text-[11px] text-gray-500">Total transactions</p>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="group flex flex-col rounded-2xl bg-[#eee9ff] p-3.5 no-underline dark:bg-purple-500/10">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-purple-600 shadow-sm"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg></span>
                    <span class="text-purple-600">›</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase text-purple-700/80">Store Revenue Today</p>
                <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90">{{ $money($storeTodayRevenue) }}</p>
                <p class="mt-0.5 text-[11px] text-gray-500">Total sales amount</p>
            </a>
        </div>

        <a href="{{ $shiftHref }}" class="cashier-hero cashier-hero--shift">
            <div class="cashier-hero__row">
                <span class="cashier-hero__icon"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                <div class="cashier-hero__body">
                    <p class="cashier-hero__title">{{ $openShift ? 'Manage Shift' : 'Open Shift' }}</p>
                    <p class="cashier-hero__muted">{{ $openShift ? 'Shift open since '.$openShift->opened_at?->format('h:i A') : 'Start your cashier shift' }}</p>
                    <p class="cashier-hero__faint">Track your sales and performance</p>
                </div>
                <span class="cashier-hero__arrow">→</span>
            </div>
        </a>

        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Quick Actions</h2>
                <a href="{{ route('admin.pos.index') }}" class="text-sm font-semibold text-brand-600">See All →</a>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                @if(auth()->user()?->hasPermission('manage_products'))
                <a href="{{ $productsHref }}" class="rounded-2xl bg-[#e8f0fe] p-3.5 no-underline"><p class="text-sm font-bold text-gray-800">View Products</p><p class="text-[11px] text-gray-500">Browse inventory</p></a>
                @endif
                <a href="{{ route('admin.orders.index') }}" class="rounded-2xl bg-[#e7f8ef] p-3.5 no-underline"><p class="text-sm font-bold text-gray-800">Sales History</p><p class="text-[11px] text-gray-500">View past sales</p></a>
                <a href="{{ $printHref }}" class="rounded-2xl bg-[#fde8e8] p-3.5 no-underline"><p class="text-sm font-bold text-gray-800">Print Receipt</p><p class="text-[11px] text-gray-500">{{ $lastReceipt?->order_number ?: 'No sales yet' }}</p></a>
                @if(auth()->user()?->hasPermission('manage_coupons'))
                <a href="{{ $discountHref }}" class="rounded-2xl bg-[#fce8f3] p-3.5 no-underline"><p class="text-sm font-bold text-gray-800">Apply Discount</p><p class="text-[11px] text-gray-500">Offers &amp; coupons</p></a>
                @elseif(auth()->user()?->hasPermission('manage_customers'))
                <a href="{{ route('admin.customers.index') }}" class="rounded-2xl bg-[#fce8f3] p-3.5 no-underline"><p class="text-sm font-bold text-gray-800">Customers</p><p class="text-[11px] text-gray-500">Shop customers</p></a>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent Sales</h2>
                <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="text-sm font-semibold text-brand-600">View All →</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentOrders as $order)
                    <li>
                        <a href="{{ route('admin.pos.receipt', $order) }}" class="flex items-center gap-3 px-4 py-3.5 no-underline">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">#{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-400">{{ $order->created_at?->format('h:i A') }} · {{ (int) ($order->items_count ?? 0) }} items</p>
                            </div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white/90">{{ $money($order->total_amount) }}</p>
                            <span class="text-gray-300">›</span>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-400">No POS sales yet today.</li>
                @endforelse
            </ul>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('admin.holds.index') }}" class="rounded-2xl border border-gray-200 bg-white px-2 py-3 text-center no-underline dark:border-gray-800"><p class="text-xs font-bold text-gray-800">Holds</p></a>
            @if(auth()->user()?->hasPermission('process_return'))
            <a href="{{ route('admin.returns.index') }}" class="rounded-2xl border border-gray-200 bg-white px-2 py-3 text-center no-underline dark:border-gray-800"><p class="text-xs font-bold text-gray-800">Returns</p></a>
            @endif
            <a href="{{ route('admin.training') }}" class="rounded-2xl border border-gray-200 bg-white px-2 py-3 text-center no-underline dark:border-gray-800"><p class="text-xs font-bold text-gray-800">Training</p></a>
        </div>
    </div>
</div>

@push('scripts')
@php
    $cashierPayChart = $paymentChart ?? ['labels' => [], 'series' => [], 'colors' => [], 'total' => 0];
@endphp
<script>
window.cashierPayChart = @json($cashierPayChart);
document.addEventListener('DOMContentLoaded', function () {
    if (!window.ApexCharts) return;
    const el = document.querySelector('#cashier-pay-chart');
    if (!el) return;
    const data = window.cashierPayChart || {};
    const series = (data.series || []).map(Number);
    const total = Number(data.total || 0);
    new ApexCharts(el, {
        chart: { type: 'donut', height: 240, fontFamily: 'Outfit, sans-serif' },
        labels: data.labels || [],
        series: series.length ? series : [0, 0, 0, 1],
        colors: data.colors || ['#12B76A', '#465FFF', '#7A5AF8', '#F79009'],
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { width: 0 },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '12px', color: '#6b7280', offsetY: 18 },
                        value: {
                            show: true,
                            fontSize: '18px',
                            fontWeight: 700,
                            color: '#1f2937',
                            offsetY: -10,
                            formatter: function () { return 'KES ' + total.toLocaleString(); }
                        },
                        total: {
                            show: true,
                            label: 'Revenue',
                            fontSize: '12px',
                            color: '#6b7280',
                            formatter: function () { return 'KES ' + total.toLocaleString(); }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: { formatter: function (val) { return 'KES ' + Number(val).toLocaleString(); } }
        }
    }).render();
});
</script>
@endpush
@endsection
