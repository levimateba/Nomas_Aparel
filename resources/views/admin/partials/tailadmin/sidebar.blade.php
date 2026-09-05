@php
    $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
        ? $settings->getRawOriginal('logo')
        : ($settings->logo ?? null);
    $adminLogo = $settings->showsLogoOnSidebar()
        ? (\App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null))
        : null;
@endphp

<aside id="sidebar"
    class="fixed inset-y-0 start-0 z-99999 flex h-screen w-[90px] max-xl:-translate-x-full flex-col border-r border-white/5 bg-[#1C2434] px-5 text-gray-100 transition-all duration-300 ease-in-out [.sidebar-expanded_&]:min-w-[290px]"
    x-data="{
        open: {
            inventory: {{ $inventoryOpen ? 'true' : 'false' }},
            sales: {{ $salesDeskOpen ? 'true' : 'false' }},
            partners: {{ $partnersOpen ? 'true' : 'false' }},
            finance: {{ $financeOpen ? 'true' : 'false' }},
            reports: {{ $reportsOpen ? 'true' : 'false' }},
            admin: {{ $administrationOpen ? 'true' : 'false' }},
            settings: {{ $userSettingsOpen ? 'true' : 'false' }},
            cms: {{ $cmsOpen ? 'true' : 'false' }},
        },
        toggle(key) {
            const next = !this.open[key];
            Object.keys(this.open).forEach(k => this.open[k] = false);
            this.open[key] = next;
        }
    }"
    :class="{
        'max-xl:!translate-x-0': $store.sidebar.isMobileOpen
    }">

    <div class="flex items-center gap-2 pb-7 pt-8"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-between'">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5">
            @if(!empty($adminLogo))
                <img src="{{ $adminLogo }}" alt="{{ $settings->site_name }}" class="h-10 w-10 rounded-xl object-cover ring-1 ring-brand-500/40">
            @else
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-lg font-bold text-white">★</span>
            @endif
            <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen" class="min-w-0">
                <span class="block truncate text-sm font-semibold text-white">{{ $settings->site_name }}</span>
                <span class="block truncate text-xs text-gray-400">{{ $settings->site_tagline ?: 'Admin Panel' }}</span>
            </span>
        </a>
    </div>

    <div class="no-scrollbar flex flex-1 flex-col overflow-y-auto duration-300 ease-linear">
        <nav class="mb-6">
            <div class="flex flex-col gap-4">
                <div>
                    <h2 class="mb-4 flex text-xs uppercase leading-[20px] text-gray-500"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Menu</span>
                        <span x-show="!($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">···</span>
                    </h2>
                    <ul class="flex flex-col gap-1">
                        @if($can('view_dashboard'))
                        <li>
                            <a href="{{ route('admin.dashboard') }}"
                               class="menu-item group {{ request()->routeIs('admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <span class="{{ request()->routeIs('admin.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                                    @include('admin.partials.icon', ['name' => 'home'])
                                </span>
                                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Dashboard</span>
                            </a>
                        </li>
                        @endif

                        @if($can('create_sale'))
                        <li>
                            <a href="{{ route('admin.cashier.home') }}"
                               class="menu-item group {{ request()->routeIs('admin.cashier.home') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <span class="{{ request()->routeIs('admin.cashier.home') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                                    @include('admin.partials.icon', ['name' => 'profile'])
                                </span>
                                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Cashier Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pos.index') }}"
                               class="menu-item group {{ request()->routeIs('admin.pos.index') && ! request()->routeIs('admin.pos.scan') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <span class="{{ request()->routeIs('admin.pos.index') && ! request()->routeIs('admin.pos.scan') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                                    @include('admin.partials.icon', ['name' => 'pos'])
                                </span>
                                <span class="menu-item-text flex items-center gap-2" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">
                                    POS Terminal <span class="nav-kbd !border-white/10 !bg-white/5 !text-gray-400">F2</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pos.scan') }}"
                               class="menu-item group {{ request()->routeIs('admin.pos.scan') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <span class="{{ request()->routeIs('admin.pos.scan') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                                    @include('admin.partials.icon', ['name' => 'coupons'])
                                </span>
                                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Scan &amp; Sell</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                @include('admin.partials.tailadmin.nav-groups')
            </div>
        </nav>

        <div class="mt-auto mb-6 space-y-3" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">
            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="menu-item menu-item-inactive group">
                <span class="menu-item-icon-inactive">@include('admin.partials.icon', ['name' => 'store'])</span>
                <span class="menu-item-text">Visit Store</span>
            </a>
        </div>
    </div>
</aside>
