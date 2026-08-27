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

        $superAdmin = Role::query()->firstOrCreate(
            ['name' => 'Super Admin'],
            ['slug' => 'super-admin', 'description' => 'Full access to every module.']
        );
        $admin = Role::query()->firstOrCreate(
            ['name' => 'Admin'],
            ['slug' => 'admin', 'description' => 'Full administrative access.']
        );
        $manager = Role::query()->firstOrCreate(
            ['name' => 'Manager'],
            ['slug' => 'manager', 'description' => 'Store operations without deleting users or restoring backups.']
        );
        $cashier = Role::query()->firstOrCreate(
            ['name' => 'Cashier'],
            ['slug' => 'cashier', 'description' => 'In-store POS sales.']
        );
        $stockManager = Role::query()->firstOrCreate(
            ['name' => 'Stock Manager'],
            ['slug' => 'stock-manager', 'description' => 'Products, categories, and stocktakes.']
        );

        $storeCore = [
            'view_dashboard', 'create_sale', 'view_sales', 'manage_products', 'manage_pos_categories',
            'manage_stocktakes', 'approve_stocktakes', 'manage_coupons', 'manage_vendors',
            'view_pos_reports', 'view_cashier_performance', 'process_return', 'manage_shifts',
            'manage_blog', 'manage_reviews', 'backup_database', 'restore_database',
        ];
        $userAdmin = [
            'view_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles_to_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles', 'assign_permissions_to_roles',
            'view_permissions', 'create_permissions', 'edit_permissions', 'delete_permissions',
            'manage_system_settings', 'view_user_groups', 'manage_user_groups',
        ];

        $sync($superAdmin, $allPermissions->keys()->all());
        $sync($admin, array_merge($storeCore, $userAdmin));
        $sync($manager, array_merge(array_values(array_diff($storeCore, ['restore_database'])), [
            'view_users', 'create_users', 'edit_users', 'assign_roles_to_users',
            'view_roles', 'view_permissions', 'manage_system_settings',
        ]));
        $sync($cashier, ['view_dashboard', 'create_sale', 'view_sales', 'process_return', 'manage_shifts']);
        $sync($stockManager, [
            'view_dashboard', 'manage_products', 'manage_pos_categories',
            'manage_stocktakes', 'view_pos_reports',
        ]);

        $staff = Role::query()->where('slug', 'staff')->first();
        if ($staff) {
            $sync($staff, [
                'view_dashboard', 'create_sale', 'view_sales', 'manage_products',
                'manage_pos_categories', 'manage_stocktakes', 'manage_blog', 'manage_reviews',
                'view_pos_reports', 'process_return', 'manage_shifts',
            ]);
        }

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
