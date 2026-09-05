<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::catalog() as $name => $meta) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name, '_'),
                    'description' => $meta['description'] ?? null,
                ]
            );
        }

        $allPermissions = Permission::query()->pluck('id', 'name');
        $sync = function (Role $role, array $permissionNames) use ($allPermissions): void {
            $ids = collect($permissionNames)
                ->map(fn (string $name) => $allPermissions[$name] ?? null)
                ->filter()
                ->values()
                ->all();
            $role->permissions()->sync($ids);
        };

        $superAdmin = Role::query()->updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full access to every module.']
        );
        $admin = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Full store + staff administration.']
        );
        $manager = Role::query()->updateOrCreate(
            ['slug' => 'manager'],
            ['name' => 'Store Manager', 'description' => 'Run the store: sales, inventory, employees & customers — no Users/Roles/system admin.']
        );
        // Keep legacy "Manager" name synced if an old row still exists under that name
        Role::query()->where('name', 'Manager')->where('slug', '!=', 'manager')->update([
            'name' => 'Store Manager',
            'slug' => 'manager',
        ]);

        $cashier = Role::query()->updateOrCreate(
            ['slug' => 'cashier'],
            ['name' => 'Cashier', 'description' => 'POS sales, shifts, customers, and returns only.']
        );
        $stockManager = Role::query()->updateOrCreate(
            ['slug' => 'stock-manager'],
            ['name' => 'Stock Manager', 'description' => 'Products, purchasing, transfers, and stock control — no POS sales.']
        );
        $staff = Role::query()->updateOrCreate(
            ['slug' => 'staff'],
            ['name' => 'Staff', 'description' => 'Limited sales helper: sell and view sales/shifts only.']
        );

        // Cashier: sell, customers, returns, own shifts
        $cashierPermissions = [
            'view_dashboard',
            'create_sale',
            'view_sales',
            'process_return',
            'manage_shifts',
            'manage_customers',
        ];

        // Stock Manager: inventory + purchasing — no POS sales, no full sales reports
        $stockManagerPermissions = [
            'view_dashboard',
            'manage_products',
            'manage_pos_categories',
            'manage_stocktakes',
            'view_inventory',
            'create_stock_transfers',
            'complete_stock_transfers',
            'adjust_stock',
            'view_stock_movements',
            'manage_stock_locations',
            'manage_suppliers',
            'manage_purchases',
            'manage_purchase_orders',
        ];

        // Store Manager: run the store — no Users / Roles / system admin
        $managerPermissions = [
            'view_dashboard',
            'create_sale',
            'view_sales',
            'manage_products',
            'manage_pos_categories',
            'manage_stocktakes',
            'approve_stocktakes',
            'view_inventory',
            'create_stock_transfers',
            'approve_stock_transfers',
            'complete_stock_transfers',
            'adjust_stock',
            'view_stock_movements',
            'manage_stock_locations',
            'manage_coupons',
            'manage_customers',
            'manage_employees',
            'manage_expenses',
            'manage_suppliers',
            'manage_purchases',
            'manage_purchase_orders',
            'approve_purchase_orders',
            'view_pos_reports',
            'view_cashier_performance',
            'process_return',
            'manage_shifts',
            'manage_loyalty',
        ];

        // Staff: sell and view own sales/shifts only (no returns, no customer CRM)
        $staffPermissions = [
            'view_dashboard',
            'create_sale',
            'view_sales',
            'manage_shifts',
        ];

        // Admin: store ops + users/roles/settings/website (Super Admin still gets everything)
        $adminPermissions = array_values(array_unique(array_merge($managerPermissions, [
            'manage_vendors',
            'manage_blog',
            'manage_reviews',
            'manage_website',
            'manage_system_settings',
            'view_audit_logs',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'assign_roles_to_users',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'assign_permissions_to_roles',
            'view_permissions',
            'view_user_groups',
            'manage_user_groups',
            'backup_database',
            'restore_database',
        ])));

        $sync($superAdmin, $allPermissions->keys()->all());
        $sync($admin, $adminPermissions);
        $sync($manager, $managerPermissions);
        $sync($cashier, $cashierPermissions);
        $sync($stockManager, $stockManagerPermissions);
        $sync($staff, $staffPermissions);

        if (Schema::hasTable('role_user')) {
            User::query()->whereNotNull('role_id')->each(function (User $user) {
                $user->roles()->syncWithoutDetaching([$user->role_id]);
            });

            User::query()->where('is_admin', true)->each(function (User $user) use ($superAdmin) {
                $user->roles()->syncWithoutDetaching([$superAdmin->id]);
                $user->update(['role_id' => $superAdmin->id, 'is_admin' => true]);
            });
        }
    }
}
