<?php

return [
    'catalog' => [
        'view_dashboard' => ['group' => 'Store', 'label' => 'View dashboard', 'description' => 'Access the admin dashboard.'],
        'create_sale' => ['group' => 'Store', 'label' => 'Create sale', 'description' => 'Use the in-store POS and complete sales.'],
        'view_sales' => ['group' => 'Store', 'label' => 'View sales', 'description' => 'Browse orders, invoices, and receipts.'],
        'manage_products' => ['group' => 'Store', 'label' => 'Manage products', 'description' => 'Create and update products.'],
        'manage_pos_categories' => ['group' => 'Store', 'label' => 'Manage categories', 'description' => 'Create and update product categories.'],
        'manage_stocktakes' => ['group' => 'Store', 'label' => 'Manage stocktakes', 'description' => 'Create and count physical stocktakes.'],
        'approve_stocktakes' => ['group' => 'Store', 'label' => 'Approve stocktakes', 'description' => 'Approve stocktake variances and apply stock.'],
        'manage_coupons' => ['group' => 'Store', 'label' => 'Manage coupons', 'description' => 'Create and update discount coupons.'],
        'manage_vendors' => ['group' => 'Store', 'label' => 'Manage vendors', 'description' => 'Manage vendors and payouts.'],
        'view_pos_reports' => ['group' => 'Store', 'label' => 'View reports', 'description' => 'View store sales and commission reports.'],
        'view_cashier_performance' => ['group' => 'Store', 'label' => 'View cashier performance', 'description' => 'View cashier sales, refunds, and shift variances.'],
        'process_return' => ['group' => 'Store', 'label' => 'Process return', 'description' => 'Refund POS items and restore stock.'],
        'manage_shifts' => ['group' => 'Store', 'label' => 'Manage shifts', 'description' => 'Open, close, and review cashier shifts.'],
        'manage_blog' => ['group' => 'Store', 'label' => 'Manage blog', 'description' => 'Create and update blog posts.'],
        'manage_reviews' => ['group' => 'Store', 'label' => 'Manage reviews', 'description' => 'Moderate product reviews and Q&A.'],
        'backup_database' => ['group' => 'Store', 'label' => 'Backup database', 'description' => 'Create and download database backups.'],
        'restore_database' => ['group' => 'Store', 'label' => 'Restore database', 'description' => 'Restore the database from a backup file.'],

        'view_users' => ['group' => 'Users', 'label' => 'View users', 'description' => 'Browse staff and customer accounts.'],
        'create_users' => ['group' => 'Users', 'label' => 'Create users', 'description' => 'Add staff accounts.'],
        'edit_users' => ['group' => 'Users', 'label' => 'Edit users', 'description' => 'Update staff accounts.'],
        'delete_users' => ['group' => 'Users', 'label' => 'Delete users', 'description' => 'Remove staff accounts.'],
        'assign_roles_to_users' => ['group' => 'Users', 'label' => 'Assign roles to users', 'description' => 'Link roles to staff accounts.'],

        'view_roles' => ['group' => 'Roles & Permissions', 'label' => 'View roles', 'description' => 'Browse system roles.'],
        'create_roles' => ['group' => 'Roles & Permissions', 'label' => 'Create roles', 'description' => 'Add new roles.'],
        'edit_roles' => ['group' => 'Roles & Permissions', 'label' => 'Edit roles', 'description' => 'Update role details.'],
        'delete_roles' => ['group' => 'Roles & Permissions', 'label' => 'Delete roles', 'description' => 'Remove roles.'],
        'assign_permissions_to_roles' => ['group' => 'Roles & Permissions', 'label' => 'Assign permissions to roles', 'description' => 'Choose which permissions each role has.'],
        'view_permissions' => ['group' => 'Roles & Permissions', 'label' => 'View permissions', 'description' => 'Browse the permission catalogue.'],
        'create_permissions' => ['group' => 'Roles & Permissions', 'label' => 'Create permissions', 'description' => 'Add new permission keys.'],
        'edit_permissions' => ['group' => 'Roles & Permissions', 'label' => 'Edit permissions', 'description' => 'Rename permission keys.'],
        'delete_permissions' => ['group' => 'Roles & Permissions', 'label' => 'Delete permissions', 'description' => 'Remove permission keys.'],

        'manage_system_settings' => ['group' => 'Settings', 'label' => 'Manage system settings', 'description' => 'Update branding and store settings.'],
        'view_user_groups' => ['group' => 'Settings', 'label' => 'View user groups', 'description' => 'Browse user groups.'],
        'manage_user_groups' => ['group' => 'Settings', 'label' => 'Manage user groups', 'description' => 'Create and update user groups.'],
    ],
];
