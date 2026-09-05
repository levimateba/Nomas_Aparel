@php
    $submenuLink = function (bool $active) {
        return $active
            ? 'menu-dropdown-item menu-dropdown-item-active'
            : 'menu-dropdown-item menu-dropdown-item-inactive';
    };
@endphp

{{-- Inventory --}}
@if($can('manage_products') || $can('manage_pos_categories') || $can('manage_stocktakes') || $can('manage_purchases') || $can('manage_purchase_orders') || $can('view_inventory') || $can('create_stock_transfers') || $can('adjust_stock') || $can('view_stock_movements') || $can('manage_stock_locations'))
<div>
    <h2 class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Inventory</span>
        <span x-show="!($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">···</span>
    </h2>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('inventory')" class="menu-item group w-full"
                :class="open.inventory ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.inventory ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'stocktake'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Stock &amp; Catalog</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.inventory ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.inventory && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    @if($can('manage_products'))
                    <li><a href="{{ route('admin.products.index') }}" class="{{ $submenuLink(request()->routeIs('admin.products.*')) }}">Products</a></li>
                    @endif
                    @if($can('view_inventory'))
                    <li><a href="{{ route('admin.stock-overview.index') }}" class="{{ $submenuLink(request()->routeIs('admin.stock-overview.*')) }}">Stock Overview</a></li>
                    <li><a href="{{ route('admin.reports.stock-valuation') }}" class="{{ $submenuLink(request()->routeIs('admin.reports.stock-valuation*')) }}">Stock Valuation</a></li>
                    @endif
                    @if($can('create_stock_transfers'))
                    <li><a href="{{ route('admin.stock-transfers.index') }}" class="{{ $submenuLink(request()->routeIs('admin.stock-transfers.*')) }}">Stock Transfers</a></li>
                    @endif
                    @if($can('adjust_stock'))
                    <li><a href="{{ route('admin.stock-adjustments.create') }}" class="{{ $submenuLink(request()->routeIs('admin.stock-adjustments.*')) }}">Stock Adjustments</a></li>
                    @endif
                    @if($can('view_stock_movements'))
                    <li><a href="{{ route('admin.stock-movements.index') }}" class="{{ $submenuLink(request()->routeIs('admin.stock-movements.*')) }}">Stock Movements</a></li>
                    @endif
                    @if($can('manage_stock_locations'))
                    <li><a href="{{ route('admin.stock-locations.index') }}" class="{{ $submenuLink(request()->routeIs('admin.stock-locations.*')) }}">Locations</a></li>
                    @endif
                    @if($can('manage_pos_categories'))
                    <li><a href="{{ route('admin.categories.index') }}" class="{{ $submenuLink(request()->routeIs('admin.categories.*')) }}">Categories</a></li>
                    @endif
                    @if($can('manage_products'))
                    <li><a href="{{ route('admin.brands.index') }}" class="{{ $submenuLink(request()->routeIs('admin.brands.*')) }}">Brands</a></li>
                    @endif
                    @if($can('manage_purchases'))
                    <li><a href="{{ route('admin.purchases.index') }}" class="{{ $submenuLink(request()->routeIs('admin.purchases.*')) }}">Purchases</a></li>
                    @endif
                    @if($can('manage_stocktakes'))
                    <li><a href="{{ route('admin.stock-takes.index') }}" class="{{ $submenuLink(request()->routeIs('admin.stock-takes.*')) }}">Stocktake</a></li>
                    @endif
                    @if($can('manage_purchase_orders') || $can('manage_purchases'))
                    <li><a href="{{ route('admin.purchase-orders.index') }}" class="{{ $submenuLink(request()->routeIs('admin.purchase-orders.*')) }}">Purchase Orders</a></li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</div>
@endif

{{-- Sales Desk --}}
@if($can('view_sales') || $can('process_return') || $can('create_sale') || $can('manage_coupons') || $can('manage_shifts'))
<div>
    <h2 class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Sales</span>
        <span x-show="!($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">···</span>
    </h2>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('sales')" class="menu-item group w-full"
                :class="open.sales ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.sales ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'orders'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Sales Desk</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.sales ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.sales && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    @if($can('create_sale'))
                    <li><a href="{{ route('admin.holds.index') }}" class="{{ $submenuLink(request()->routeIs('admin.holds.*')) }}">Hold Sales</a></li>
                    @endif
                    @if($can('view_sales'))
                    <li><a href="{{ route('admin.orders.index') }}" class="{{ $submenuLink(request()->routeIs('admin.orders.*')) }}">Sales History</a></li>
                    @endif
                    @if($can('process_return'))
                    <li><a href="{{ route('admin.returns.index') }}" class="{{ $submenuLink(request()->routeIs('admin.returns.*')) }}">Returns</a></li>
                    @endif
                    @if($can('manage_coupons'))
                    <li><a href="{{ route('admin.coupons.index') }}" class="{{ $submenuLink(request()->routeIs('admin.coupons.*')) }}">Coupons</a></li>
                    @endif
                    @if($can('manage_shifts'))
                    <li><a href="{{ route('admin.shifts.index') }}" class="{{ $submenuLink(request()->routeIs('admin.shifts.*')) }}">Cashier Shifts</a></li>
                    @endif
                    @if($can('create_sale'))
                    <li><a href="{{ route('admin.training') }}" class="{{ $submenuLink(request()->routeIs('admin.training')) }}">Cashier Training</a></li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</div>
@endif

{{-- Partners --}}
@if($can('manage_customers') || $can('manage_employees') || $can('manage_suppliers') || $can('manage_vendors') || $can('manage_loyalty'))
<div>
    <h2 class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Partners</span>
        <span x-show="!($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">···</span>
    </h2>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('partners')" class="menu-item group w-full"
                :class="open.partners ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.partners ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'customers'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">People</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.partners ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.partners && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    @if($can('manage_employees'))
                    <li><a href="{{ route('admin.employees.index') }}" class="{{ $submenuLink(request()->routeIs('admin.employees.*')) }}">Employees</a></li>
                    @endif
                    @if($can('manage_customers'))
                    <li><a href="{{ route('admin.customers.index') }}" class="{{ $submenuLink(request()->routeIs('admin.customers.*')) }}">Customers</a></li>
                    @endif
                    @if($can('manage_loyalty'))
                    <li><a href="{{ route('admin.loyalty.history') }}" class="{{ $submenuLink(request()->routeIs('admin.loyalty.history')) }}">Loyalty Members</a></li>
                    @endif
                    @if($can('manage_suppliers'))
                    <li><a href="{{ route('admin.suppliers.index') }}" class="{{ $submenuLink(request()->routeIs('admin.suppliers.*')) }}">Suppliers</a></li>
                    @endif
                    @if($can('manage_vendors'))
                    <li><a href="{{ route('admin.vendors.index') }}" class="{{ $submenuLink(request()->routeIs('admin.vendors.*')) }}">Vendors</a></li>
                    <li><a href="{{ route('admin.vendor-payouts.index') }}" class="{{ $submenuLink(request()->routeIs('admin.vendor-payouts.*')) }}">Payouts</a></li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</div>
@endif

@if($can('manage_expenses'))
<div>
    <ul class="flex flex-col gap-1">
        <li>
            <a href="{{ route('admin.expenses.index') }}"
               class="menu-item group {{ request()->routeIs('admin.expenses.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                <span class="{{ request()->routeIs('admin.expenses.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                    @include('admin.partials.icon', ['name' => 'reports'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Expenses</span>
            </a>
        </li>
    </ul>
</div>
@endif

@if($can('view_pos_reports'))
<div>
    <h2 class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Reports</span>
        <span x-show="!($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">···</span>
    </h2>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('reports')" class="menu-item group w-full"
                :class="open.reports ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.reports ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'reports'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Analytics</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.reports ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.reports && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    <li><a href="{{ route('admin.reports.index') }}" class="{{ $submenuLink(request()->routeIs('admin.reports.index') && ! request()->filled('preset')) }}">Sales &amp; Inventory</a></li>
                    <li><a href="{{ route('admin.reports.stock-valuation') }}" class="{{ $submenuLink(request()->routeIs('admin.reports.stock-valuation*')) }}">Stock Valuation</a></li>
                    <li><a href="{{ route('admin.reports.online-sales-by-location') }}" class="{{ $submenuLink(request()->routeIs('admin.reports.online-sales-by-location')) }}">Online by Location</a></li>
                    @if($can('view_cashier_performance'))
                    <li><a href="{{ route('admin.reports.cashier') }}" class="{{ $submenuLink(request()->routeIs('admin.reports.cashier')) }}">Cashier Performance</a></li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</div>
@endif

@if($can('view_users') || $can('view_roles') || $can('view_permissions') || $can('view_user_groups') || $can('manage_user_groups') || $can('backup_database') || $can('restore_database') || $can('view_audit_logs'))
<div>
    <h2 class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Admin</span>
        <span x-show="!($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">···</span>
    </h2>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('admin')" class="menu-item group w-full"
                :class="open.admin ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.admin ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'roles'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Administration</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.admin ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.admin && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    @if($can('view_users'))
                    <li><a href="{{ route('admin.users.index') }}" class="{{ $submenuLink(request()->routeIs('admin.users.*')) }}">Users</a></li>
                    @endif
                    @if($can('view_roles'))
                    <li><a href="{{ route('admin.roles.index') }}" class="{{ $submenuLink(request()->routeIs('admin.roles.*')) }}">Roles</a></li>
                    @endif
                    @if($can('view_permissions'))
                    <li><a href="{{ route('admin.permissions.index') }}" class="{{ $submenuLink(request()->routeIs('admin.permissions.*')) }}">Permissions</a></li>
                    @endif
                    @if($can('view_user_groups') || $can('manage_user_groups'))
                    <li><a href="{{ route('admin.user-groups.index') }}" class="{{ $submenuLink(request()->routeIs('admin.user-groups.*')) }}">User Groups</a></li>
                    @endif
                    @if($can('view_audit_logs'))
                    <li><a href="{{ route('admin.audit-logs.index') }}" class="{{ $submenuLink(request()->routeIs('admin.audit-logs.*')) }}">Audit Logs</a></li>
                    @endif
                    @if($can('backup_database') || $can('restore_database'))
                    <li><a href="{{ route('admin.backups.index') }}" class="{{ $submenuLink(request()->routeIs('admin.backups.*')) }}">Backup &amp; Restore</a></li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</div>
@endif

@if($can('manage_system_settings') || $can('manage_loyalty'))
<div>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('settings')" class="menu-item group w-full"
                :class="open.settings ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.settings ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'settings'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Settings</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.settings ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.settings && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    @if($can('manage_system_settings'))
                    <li><a href="{{ route('admin.settings.edit') }}" class="{{ $submenuLink(request()->routeIs('admin.settings.*')) }}">Store Settings</a></li>
                    @endif
                    @if($can('manage_loyalty'))
                    <li><a href="{{ route('admin.loyalty.settings') }}" class="{{ $submenuLink(request()->routeIs('admin.loyalty.settings*')) }}">Customer Loyalty</a></li>
                    <li><a href="{{ route('admin.loyalty.dashboard') }}" class="{{ $submenuLink(request()->routeIs('admin.loyalty.dashboard') || request()->routeIs('admin.loyalty.history')) }}">Loyalty Dashboard</a></li>
                    @endif
                    <li><a href="{{ route('admin.profile.edit') }}" class="{{ $submenuLink(request()->routeIs('admin.profile.*')) }}">Profile</a></li>
                </ul>
            </div>
        </li>
    </ul>
</div>
@else
<div>
    <ul class="flex flex-col gap-1">
        <li>
            <a href="{{ route('admin.profile.edit') }}"
               class="menu-item group {{ request()->routeIs('admin.profile.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                <span class="{{ request()->routeIs('admin.profile.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                    @include('admin.partials.icon', ['name' => 'settings'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Profile</span>
            </a>
        </li>
    </ul>
</div>
@endif

@if($can('manage_blog') || $can('manage_reviews') || $can('manage_website'))
<div>
    <ul class="flex flex-col gap-1">
        <li>
            <button type="button" @click="toggle('cms')" class="menu-item group w-full"
                :class="open.cms ? 'menu-item-active' : 'menu-item-inactive'">
                <span :class="open.cms ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                    @include('admin.partials.icon', ['name' => 'blog'])
                </span>
                <span class="menu-item-text" x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen">Website</span>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isMobileOpen"
                     class="ml-auto h-5 w-5 transition-transform" :class="open.cms ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                     viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open.cms && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" x-collapse>
                <ul class="mt-2 ml-9 space-y-1">
                    @if($can('manage_blog'))
                    <li><a href="{{ route('admin.blog.index') }}" class="{{ $submenuLink(request()->routeIs('admin.blog.*')) }}">Blog</a></li>
                    @endif
                    @if($can('manage_reviews'))
                    <li><a href="{{ route('admin.reviews.index') }}" class="{{ $submenuLink(request()->routeIs('admin.reviews.*')) }}">Reviews</a></li>
                    <li><a href="{{ route('admin.questions.index') }}" class="{{ $submenuLink(request()->routeIs('admin.questions.*')) }}">Q&amp;A</a></li>
                    @endif
                    @if($can('manage_website'))
                    <li><a href="{{ route('admin.enquiries.index') }}" class="{{ $submenuLink(request()->routeIs('admin.enquiries.*')) }}">Enquiries @if(($enquiryCount ?? 0) > 0)<span class="nav-badge">{{ $enquiryCount }}</span>@endif</a></li>
                    <li><a href="{{ route('admin.newsletter-subscribers.index') }}" class="{{ $submenuLink(request()->routeIs('admin.newsletter-subscribers.*')) }}">Subscribers</a></li>
                    <li><a href="{{ route('admin.contact.index') }}" class="{{ $submenuLink(request()->routeIs('admin.contact.*')) }}">Contacts</a></li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</div>
@endif
