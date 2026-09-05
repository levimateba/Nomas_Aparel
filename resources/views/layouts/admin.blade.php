<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | {{ $settings->site_name ?? 'Nomas Apparel' }}</title>
    @include('partials.pwa-head', ['pwaContext' => 'admin'])
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    <style>[x-cloak]{display:none!important}</style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    this.theme = savedTheme === 'dark' ? 'dark' : 'light';
                    this.updateTheme();
                },
                theme: 'light',
                resolvedTheme: 'light',
                set(value) {
                    this.theme = value === 'dark' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                toggle() {
                    this.set(this.resolvedTheme === 'dark' ? 'light' : 'dark');
                },
                updateTheme() {
                    const isDark = this.theme === 'dark';
                    document.documentElement.classList.toggle('dark', isDark);
                    this.resolvedTheme = isDark ? 'dark' : 'light';
                }
            });

            Alpine.store('sidebar', {
                isExpanded: true,
                isMobileOpen: false,
                isHovered: false,
                init() {
                    const savedState = localStorage.getItem('sidebarExpanded');
                    if (window.innerWidth >= 1280) {
                        this.isExpanded = savedState === null ? true : savedState === 'true';
                    } else {
                        this.isExpanded = false;
                    }
                    window.addEventListener('resize', () => this.handleResize());
                },
                handleResize() {
                    if (window.innerWidth < 1280) {
                        this.isMobileOpen = false;
                    } else {
                        this.isMobileOpen = false;
                        const savedState = localStorage.getItem('sidebarExpanded');
                        this.isExpanded = savedState === null ? true : savedState === 'true';
                    }
                },
                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                    if (window.innerWidth >= 1280) {
                        localStorage.setItem('sidebarExpanded', this.isExpanded);
                    }
                },
                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },
                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @stack('styles')
</head>
<body class="font-outfit">
@php
    $adminUser = auth()->user();
    $adminUser?->loadMissing(['roles', 'role']);
    $can = function (?string $permission = null) use ($adminUser): bool {
        if ($permission === null) {
            return true;
        }
        if ($permission === '__full_admin__') {
            return (bool) $adminUser?->isFullAdmin();
        }

        return (bool) $adminUser?->hasPermission($permission);
    };
    $adminRole = $adminUser?->allRoleNames()->first() ?: ($adminUser?->is_admin ? 'Super Admin' : 'Staff');
    $adminInitial = strtoupper(substr($adminUser?->name ?? 'A', 0, 1));
    $adminAvatar = $adminUser?->avatarUrl();
    $enquiryCount = \Illuminate\Support\Facades\Schema::hasTable('contact_messages') ? \App\Models\ContactMessage::count() : 0;
    $pendingOrderCount = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \App\Models\Order::whereIn('status', ['pending', 'processing'])->count() : 0;
    $lowStockCount = \Illuminate\Support\Facades\Schema::hasTable('products') ? \App\Models\Product::where('is_active', true)->where('stock', '<=', 5)->count() : 0;
    $notifyCount = $pendingOrderCount + $lowStockCount + (int) $enquiryCount;
    $salesDeskOpen = request()->routeIs('admin.orders.*')
        || request()->routeIs('admin.returns.*')
        || request()->routeIs('admin.shifts.*')
        || request()->routeIs('admin.holds.*')
        || request()->routeIs('admin.training')
        || request()->routeIs('admin.coupons.*');
    $partnersOpen = request()->routeIs('admin.customers.*')
        || request()->routeIs('admin.employees.*')
        || request()->routeIs('admin.suppliers.*')
        || request()->routeIs('admin.vendors.*')
        || request()->routeIs('admin.vendor-payouts.*')
        || request()->routeIs('admin.loyalty.history');
    $financeOpen = request()->routeIs('admin.expenses.*');
    $reportsOpen = request()->routeIs('admin.reports.*');
    $inventoryOpen = request()->routeIs('admin.products.*')
        || request()->routeIs('admin.categories.*')
        || request()->routeIs('admin.stock-takes.*')
        || request()->routeIs('admin.brands.*')
        || request()->routeIs('admin.purchases.*')
        || request()->routeIs('admin.purchase-orders.*')
        || request()->routeIs('admin.stock-overview.*')
        || request()->routeIs('admin.stock-locations.*')
        || request()->routeIs('admin.stock-transfers.*')
        || request()->routeIs('admin.stock-adjustments.*')
        || request()->routeIs('admin.stock-movements.*');
    $administrationOpen = request()->routeIs('admin.users.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.permissions.*')
        || request()->routeIs('admin.user-groups.*')
        || request()->routeIs('admin.backups.*')
        || request()->routeIs('admin.audit-logs.*');
    $cmsOpen = request()->routeIs('admin.blog.*')
        || request()->routeIs('admin.reviews.*')
        || request()->routeIs('admin.questions.*')
        || request()->routeIs('admin.enquiries.*')
        || request()->routeIs('admin.newsletter-subscribers.*')
        || request()->routeIs('admin.contact.*');
    $userSettingsOpen = request()->routeIs('admin.settings.*')
        || request()->routeIs('admin.profile.*')
        || request()->routeIs('admin.loyalty.settings*')
        || request()->routeIs('admin.loyalty.dashboard');
@endphp

<div class="min-h-screen xl:flex"
     x-data
     :class="{ 'sidebar-expanded': $store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen }">
    <div
        :class="$store.sidebar.isMobileOpen ? 'block xl:hidden' : 'hidden'"
        class="fixed z-50 h-screen w-full bg-gray-900/50"
        @click="$store.sidebar.toggleMobileOpen()"
    ></div>

    @include('admin.partials.tailadmin.sidebar')

    <div class="flex-1 transition-all duration-300 ease-in-out ltr:xl:ml-[90px] [.sidebar-expanded_&]:ltr:xl:ml-[290px]">
        @include('admin.partials.tailadmin.header')

        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
            @unless(request()->routeIs('admin.dashboard'))
                @hasSection('page_header')
                    <div class="mb-5">@yield('page_header')</div>
                @else
                    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            @hasSection('breadcrumbs')
                                <nav class="mb-2 text-sm text-gray-400">@yield('breadcrumbs')</nav>
                            @endif
                            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                                @hasSection('heading')
                                    @yield('heading')
                                @else
                                    @yield('title', 'Admin')
                                @endif
                            </h1>
                            @hasSection('subheading')
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@yield('subheading')</p>
                            @endif
                        </div>
                        @hasSection('page_actions')
                            <div class="flex flex-wrap items-center gap-2">@yield('page_actions')</div>
                        @endif
                    </div>
                @endif
            @endunless

            @if(session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <p class="mt-8 mb-20 text-center text-xs text-gray-400 xl:mb-0">
                {{ $settings->site_name ?? 'Nomas Apparel' }} Admin · TailAdmin UI
            </p>
        </div>

        @include('admin.partials.tailadmin.mobile-bottom-nav')
    </div>
</div>

<script>
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'F2') return;
        const tag = (event.target && event.target.tagName) ? event.target.tagName.toLowerCase() : '';
        if (['input', 'textarea', 'select'].includes(tag) || event.target?.isContentEditable) return;
        event.preventDefault();
        window.location.href = @json(route('admin.pos.index'));
    });
</script>
@stack('scripts')
@include('partials.pwa-install', ['pwaContext' => 'admin'])
</body>
</html>
