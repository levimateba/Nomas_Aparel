@php
    $moreOpen = request()->routeIs('admin.settings.*')
        || request()->routeIs('admin.profile.*')
        || request()->routeIs('admin.users.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.permissions.*')
        || request()->routeIs('admin.employees.*')
        || request()->routeIs('admin.loyalty.*')
        || request()->routeIs('admin.backups.*')
        || request()->routeIs('admin.user-groups.*')
        || request()->routeIs('admin.customers.*');
    $dashActive = request()->routeIs('admin.dashboard');
    $salesActive = request()->routeIs('admin.orders.*') || request()->routeIs('admin.pos.*') || request()->routeIs('admin.returns.*') || request()->routeIs('admin.cashier.*');
    $inventoryActive = request()->routeIs('admin.products.*')
        || request()->routeIs('admin.categories.*')
        || request()->routeIs('admin.stock-*')
        || request()->routeIs('admin.brands.*')
        || request()->routeIs('admin.purchases.*')
        || request()->routeIs('admin.purchase-orders.*');
    $reportsActive = request()->routeIs('admin.reports.*');

    $showDash = $can('view_dashboard');
    $showSales = $can('view_sales') || $can('create_sale') || $can('process_return');
    $showInventory = $can('view_inventory') || $can('manage_products') || $can('create_stock_transfers') || $can('adjust_stock');
    $showReports = $can('view_pos_reports');
@endphp

{{-- Fixed bottom nav — mobile / tablet only (permission-gated) --}}
<nav class="fixed inset-x-0 bottom-0 z-99998 border-t border-gray-200 bg-white pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_16px_rgba(0,0,0,0.06)] xl:hidden dark:border-gray-800 dark:bg-gray-900"
     aria-label="Mobile navigation">
    <div class="mx-auto grid h-[4.25rem] max-w-lg grid-cols-5 items-stretch px-1">
        @if($showDash)
        <a href="{{ route('admin.dashboard') }}"
           class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $dashActive ? 'text-brand-600' : 'text-gray-400' }}">
            <span class="flex h-8 w-12 items-center justify-center rounded-full {{ $dashActive ? 'bg-brand-50' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"/></svg>
            </span>
            <span>Dashboard</span>
        </a>
        @else
        <span class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold text-gray-300" aria-hidden="true"></span>
        @endif

        @if($showSales)
        <a href="{{ $can('create_sale') ? route('admin.cashier.home') : ($can('view_sales') ? route('admin.orders.index') : route('admin.pos.index')) }}"
           class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $salesActive ? 'text-brand-600' : 'text-gray-400' }}">
            <span class="flex h-8 w-12 items-center justify-center rounded-full {{ $salesActive ? 'bg-brand-50' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg>
            </span>
            <span>Sales</span>
        </a>
        @else
        <span class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold text-gray-300" aria-hidden="true"></span>
        @endif

        @if($showInventory)
        <a href="{{ $can('view_inventory') ? route('admin.stock-overview.index') : route('admin.products.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $inventoryActive ? 'text-brand-600' : 'text-gray-400' }}">
            <span class="flex h-8 w-12 items-center justify-center rounded-full {{ $inventoryActive ? 'bg-brand-50' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </span>
            <span>Inventory</span>
        </a>
        @else
        <span class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold text-gray-300" aria-hidden="true"></span>
        @endif

        @if($showReports)
        <a href="{{ route('admin.reports.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $reportsActive ? 'text-brand-600' : 'text-gray-400' }}">
            <span class="flex h-8 w-12 items-center justify-center rounded-full {{ $reportsActive ? 'bg-brand-50' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
            </span>
            <span>Reports</span>
        </a>
        @else
        <span class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-semibold text-gray-300" aria-hidden="true"></span>
        @endif

        <div class="relative flex" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="flex w-full flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $moreOpen ? 'text-brand-600' : 'text-gray-400' }}">
                <span class="flex h-8 w-12 items-center justify-center rounded-full {{ $moreOpen ? 'bg-brand-50' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01"/></svg>
                </span>
                <span>More</span>
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute bottom-[calc(100%+0.5rem)] right-0 w-52 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                @if($can('manage_customers'))
                    <a href="{{ route('admin.customers.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Customers</a>
                @endif
                @if($can('manage_employees'))
                    <a href="{{ route('admin.employees.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Employees</a>
                @endif
                @if($can('view_users'))
                    <a href="{{ route('admin.users.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Users</a>
                @endif
                @if($can('manage_loyalty'))
                    <a href="{{ route('admin.loyalty.dashboard') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Loyalty</a>
                @endif
                @if($can('manage_system_settings'))
                    <a href="{{ route('admin.settings.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Settings</a>
                @endif
                <a href="{{ route('admin.profile.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Profile</a>
                <button type="button" @click="$store.sidebar.toggleMobileOpen(); open = false"
                        class="block w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">
                    Full menu
                </button>
            </div>
        </div>
    </div>
</nav>
