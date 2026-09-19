<header class="sticky top-0 z-99999 flex w-full border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
    x-data="{ notifyOpen: false, profileOpen: false }">
    <div class="flex w-full items-center justify-between gap-2 px-3 py-3 xl:px-6 lg:py-4">
        <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-4">
            <button type="button"
                class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 xl:flex lg:h-11 lg:w-11"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor"/></svg>
            </button>

            <button type="button"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-500 xl:hidden lg:h-11 lg:w-11"
                @click="$store.sidebar.toggleMobileOpen()" aria-label="Open menu">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor"/></svg>
            </button>

            {{-- Mobile brand --}}
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 flex-1 items-center gap-2 xl:hidden">
                @if($settings->logo)
                    <img src="{{ $settings->logo }}" alt="" class="h-9 w-9 shrink-0 rounded-xl object-contain">
                @else
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-sm font-black text-gray-900">N</span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold leading-tight text-gray-800 dark:text-white/90">{{ $settings->displayName() }}</span>
                    <span class="block truncate text-[10px] text-gray-400">{{ $settings->site_tagline ?: 'Style for Everyone' }}</span>
                </span>
            </a>

            @php
                $globalSearchPages = collect([
                    ['label' => 'Dashboard', 'group' => 'Home', 'url' => route('admin.dashboard'), 'keywords' => 'home overview', 'show' => true],
                    ['label' => 'Point of Sale', 'group' => 'Sales', 'url' => route('admin.pos.index'), 'keywords' => 'pos sell checkout', 'show' => $can('create_sale')],
                    ['label' => 'Scan & Sell', 'group' => 'Sales', 'url' => route('admin.pos.scan'), 'keywords' => 'barcode qr scan', 'show' => $can('create_sale')],
                    ['label' => 'Cashier Home', 'group' => 'Sales', 'url' => route('admin.cashier.home'), 'keywords' => 'cashier desk', 'show' => $can('create_sale')],
                    ['label' => 'Products', 'group' => 'Stock & Catalog', 'url' => route('admin.products.index'), 'keywords' => 'catalog inventory sku', 'show' => $can('manage_products')],
                    ['label' => 'Add Product', 'group' => 'Stock & Catalog', 'url' => route('admin.products.create'), 'keywords' => 'new create product', 'show' => $can('manage_products')],
                    ['label' => 'Stock Overview', 'group' => 'Stock & Catalog', 'url' => route('admin.stock-overview.index'), 'keywords' => 'inventory levels', 'show' => $can('view_inventory')],
                    ['label' => 'Stock Valuation', 'group' => 'Stock & Catalog', 'url' => route('admin.reports.stock-valuation'), 'keywords' => 'value inventory', 'show' => $can('view_inventory')],
                    ['label' => 'Stock Transfers', 'group' => 'Stock & Catalog', 'url' => route('admin.stock-transfers.index'), 'keywords' => 'move transfer', 'show' => $can('create_stock_transfers')],
                    ['label' => 'Stock Adjustments', 'group' => 'Stock & Catalog', 'url' => route('admin.stock-adjustments.create'), 'keywords' => 'adjust quantity', 'show' => $can('adjust_stock')],
                    ['label' => 'Stock Movements', 'group' => 'Stock & Catalog', 'url' => route('admin.stock-movements.index'), 'keywords' => 'history movements', 'show' => $can('view_stock_movements')],
                    ['label' => 'Locations', 'group' => 'Stock & Catalog', 'url' => route('admin.stock-locations.index'), 'keywords' => 'stores warehouses', 'show' => $can('manage_stock_locations')],
                    ['label' => 'Categories', 'group' => 'Stock & Catalog', 'url' => route('admin.categories.index'), 'keywords' => 'pos categories', 'show' => $can('manage_pos_categories')],
                    ['label' => 'Brands', 'group' => 'Stock & Catalog', 'url' => route('admin.brands.index'), 'keywords' => 'labels makers', 'show' => $can('manage_products')],
                    ['label' => 'Product Types', 'group' => 'Stock & Catalog', 'url' => route('admin.product-styles.index'), 'keywords' => 'styles types', 'show' => $can('manage_products')],
                    ['label' => 'Purchases', 'group' => 'Stock & Catalog', 'url' => route('admin.purchases.index'), 'keywords' => 'buying goods', 'show' => $can('manage_purchases')],
                    ['label' => 'Stocktake', 'group' => 'Stock & Catalog', 'url' => route('admin.stock-takes.index'), 'keywords' => 'count audit', 'show' => $can('manage_stocktakes')],
                    ['label' => 'Purchase Orders', 'group' => 'Stock & Catalog', 'url' => route('admin.purchase-orders.index'), 'keywords' => 'po orders', 'show' => $can('manage_purchase_orders') || $can('manage_purchases')],
                    ['label' => 'Hold Sales', 'group' => 'Sales Desk', 'url' => route('admin.holds.index'), 'keywords' => 'parked holds', 'show' => $can('create_sale')],
                    ['label' => 'Sales History', 'group' => 'Sales Desk', 'url' => route('admin.orders.index'), 'keywords' => 'orders receipts', 'show' => $can('view_sales')],
                    ['label' => 'Returns', 'group' => 'Sales Desk', 'url' => route('admin.returns.index'), 'keywords' => 'refunds', 'show' => $can('process_return')],
                    ['label' => 'Coupons', 'group' => 'Sales Desk', 'url' => route('admin.coupons.index'), 'keywords' => 'discounts promo', 'show' => $can('manage_coupons')],
                    ['label' => 'Cashier Shifts', 'group' => 'Sales Desk', 'url' => route('admin.shifts.index'), 'keywords' => 'shift open close', 'show' => $can('manage_shifts')],
                    ['label' => 'Cashier Training', 'group' => 'Sales Desk', 'url' => route('admin.training'), 'keywords' => 'learn guide', 'show' => $can('create_sale')],
                    ['label' => 'Employees', 'group' => 'People', 'url' => route('admin.employees.index'), 'keywords' => 'staff hr', 'show' => $can('manage_employees')],
                    ['label' => 'Customers', 'group' => 'People', 'url' => route('admin.customers.index'), 'keywords' => 'clients shoppers', 'show' => $can('manage_customers')],
                    ['label' => 'Loyalty Members', 'group' => 'People', 'url' => route('admin.loyalty.history'), 'keywords' => 'points cards', 'show' => $can('manage_loyalty')],
                    ['label' => 'Suppliers', 'group' => 'People', 'url' => route('admin.suppliers.index'), 'keywords' => 'vendors supply', 'show' => $can('manage_suppliers')],
                    ['label' => 'Vendors', 'group' => 'People', 'url' => route('admin.vendors.index'), 'keywords' => 'partners', 'show' => $can('manage_vendors')],
                    ['label' => 'Payouts', 'group' => 'People', 'url' => route('admin.vendor-payouts.index'), 'keywords' => 'vendor pay', 'show' => $can('manage_vendors')],
                    ['label' => 'Expenses', 'group' => 'Finance', 'url' => route('admin.expenses.index'), 'keywords' => 'costs spending', 'show' => $can('manage_expenses')],
                    ['label' => 'Sales & Inventory Reports', 'group' => 'Reports', 'url' => route('admin.reports.index'), 'keywords' => 'analytics dashboard reports', 'show' => $can('view_pos_reports')],
                    ['label' => 'Online by Location', 'group' => 'Reports', 'url' => route('admin.reports.online-sales-by-location'), 'keywords' => 'ecommerce location', 'show' => $can('view_pos_reports')],
                    ['label' => 'Cashier Performance', 'group' => 'Reports', 'url' => route('admin.reports.cashier'), 'keywords' => 'staff sales report', 'show' => $can('view_cashier_performance')],
                    ['label' => 'Users', 'group' => 'Administration', 'url' => route('admin.users.index'), 'keywords' => 'accounts logins', 'show' => $can('view_users')],
                    ['label' => 'Roles', 'group' => 'Administration', 'url' => route('admin.roles.index'), 'keywords' => 'permissions roles', 'show' => $can('view_roles')],
                    ['label' => 'Permissions', 'group' => 'Administration', 'url' => route('admin.permissions.index'), 'keywords' => 'access rights', 'show' => $can('view_permissions')],
                    ['label' => 'Audit Logs', 'group' => 'Administration', 'url' => route('admin.audit-logs.index'), 'keywords' => 'activity history', 'show' => $can('view_audit_logs')],
                    ['label' => 'Settings', 'group' => 'Administration', 'url' => route('admin.settings.edit'), 'keywords' => 'config store', 'show' => $can('manage_system_settings')],
                    ['label' => 'Profile', 'group' => 'Administration', 'url' => route('admin.profile.edit'), 'keywords' => 'account password', 'show' => true],
                ]);

                $globalSearchPages = $globalSearchPages
                    ->filter(function ($item) {
                        return ! empty($item['show']);
                    })
                    ->map(function ($item) {
                        return [
                            'label' => $item['label'],
                            'group' => $item['group'],
                            'url' => $item['url'],
                            'keywords' => $item['keywords'],
                        ];
                    })
                    ->values();

                $globalSearchPagesJson = $globalSearchPages->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $globalSearchProductsUrlJson = json_encode(route('admin.products.index'), JSON_UNESCAPED_SLASHES);
            @endphp

            <div id="admin-global-search-wrap" class="relative hidden min-w-0 flex-1 lg:block lg:max-w-md">
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-gray-400">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"/></svg>
                    </span>
                    <input
                        id="admin-global-search"
                        type="text"
                        role="combobox"
                        aria-expanded="false"
                        aria-controls="admin-global-search-results"
                        aria-autocomplete="list"
                        placeholder="Search pages, products, SKU…"
                        autocomplete="off"
                        spellcheck="false"
                        class="admin-global-search-input h-11 w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-12 pr-14 text-sm text-gray-900 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-brand-500"
                    >
                    <button type="button" id="admin-global-search-kbd" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5 text-[11px] font-semibold text-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400" title="Focus search (⌘K)" aria-label="Focus search">⌘K</button>
                </div>
                <div id="admin-global-search-results" class="admin-global-search-results absolute left-0 right-0 top-[calc(100%+6px)] z-[100000] hidden max-h-[min(70vh,420px)] overflow-y-auto rounded-xl border border-gray-200 bg-white py-2 shadow-xl dark:border-gray-700 dark:bg-gray-900" role="listbox"></div>
            </div>
            <script type="application/json" id="admin-global-search-pages">{!! $globalSearchPagesJson !!}</script>
            <script type="application/json" id="admin-global-search-products-url">{!! $globalSearchProductsUrlJson !!}</script>
        </div>

        <div class="flex shrink-0 items-center justify-end gap-1.5 sm:gap-2">
            <button type="button" @click="$store.theme.toggle()"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 sm:h-11 sm:w-11 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"
                aria-label="Toggle theme">
                <svg class="dark:hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 14.5A8.5 8.5 0 1110 3.5a7 7 0 0011 11z"/></svg>
            </button>

            <div class="relative">
                <button type="button" data-notify-btn @click="notifyOpen = !notifyOpen; profileOpen = false"
                    class="relative flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 sm:h-11 sm:w-11 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"
                    aria-label="Notifications">
                    @include('admin.partials.icon', ['name' => 'bell'])
                    @if($notifyCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-error-500 px-1 text-[10px] font-bold text-white">
                            {{ $notifyCount > 9 ? '9+' : $notifyCount }}
                        </span>
                    @endif
                </button>
                <div x-show="notifyOpen" @click.outside="notifyOpen = false" x-cloak
                     class="absolute right-0 mt-2 w-72 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    @if($can('view_sales'))
                    <a class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5" href="{{ route('admin.orders.index', ['status' => 'pending']) }}">{{ $pendingOrderCount }} pending orders</a>
                    @endif
                    @if($can('manage_products'))
                    <a class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5" href="{{ route('admin.products.index', ['stock' => 'low']) }}">{{ $lowStockCount }} low-stock products</a>
                    @endif
                    @if($can('__full_admin__'))
                    <a class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5" href="{{ route('admin.enquiries.index') }}">{{ $enquiryCount }} enquiries</a>
                    @endif
                    @if($notifyCount < 1)
                        <p class="px-3 py-2 text-sm text-gray-400">No new notifications</p>
                    @endif
                </div>
            </div>

            <div class="relative">
                <button type="button" @click="profileOpen = !profileOpen; notifyOpen = false"
                    class="flex items-center gap-2 rounded-full border border-gray-200 bg-white py-1 pe-2.5 ps-1 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                    @if(!empty($adminAvatar))
                        <img src="{{ $adminAvatar }}" alt="" class="h-8 w-8 rounded-full object-cover sm:h-9 sm:w-9">
                    @else
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white sm:h-9 sm:w-9">
                            {{ $adminInitial }}
                            <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-success-500"></span>
                        </span>
                    @endif
                    <span class="hidden text-sm font-medium text-gray-700 sm:inline dark:text-gray-300">{{ $adminUser?->name ?? 'Admin' }}</span>
                    <svg class="hidden h-4 w-4 text-gray-400 sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="profileOpen" @click.outside="profileOpen = false" x-cloak
                     class="absolute right-0 mt-2 w-56 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">
                        <strong class="block text-sm text-gray-800 dark:text-white/90">{{ $adminUser?->name ?? 'Admin' }}</strong>
                        <small class="text-xs text-gray-500">{{ $adminRole }}</small>
                    </div>
                    <a href="{{ route('admin.profile.edit') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200">My profile</a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
