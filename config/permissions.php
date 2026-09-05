<?php

return [
    'catalog' => [
        // POS & Sales
        'view_dashboard' => ['group' => 'POS & Sales', 'label' => 'View dashboard', 'description' => 'Access the admin dashboard.'],
        'create_sale' => ['group' => 'POS & Sales', 'label' => 'Create sale', 'description' => 'Use the in-store POS and complete sales.'],
        'view_sales' => ['group' => 'POS & Sales', 'label' => 'View sales', 'description' => 'Browse orders, invoices, and receipts.'],
        'process_return' => ['group' => 'POS & Sales', 'label' => 'Process return', 'description' => 'Refund POS items and restore stock.'],
        'manage_shifts' => ['group' => 'POS & Sales', 'label' => 'Manage shifts', 'description' => 'Open, close, and review cashier shifts.'],
        'manage_coupons' => ['group' => 'POS & Sales', 'label' => 'Manage coupons', 'description' => 'Create and update discount coupons.'],
        'manage_expenses' => ['group' => 'POS & Sales', 'label' => 'Manage expenses', 'description' => 'Record and review shop expenses.'],

        // Inventory
        'manage_products' => ['group' => 'Inventory', 'label' => 'Manage products', 'description' => 'Create and update products.'],
        'manage_pos_categories' => ['group' => 'Inventory', 'label' => 'Manage categories', 'description' => 'Create and update product categories.'],
        'view_inventory' => ['group' => 'Inventory', 'label' => 'View inventory', 'description' => 'View stock overview across locations.'],
        'manage_stock_locations' => ['group' => 'Inventory', 'label' => 'Manage locations', 'description' => 'Create and update inventory locations.'],
        'create_stock_transfers' => ['group' => 'Inventory', 'label' => 'Create stock transfers', 'description' => 'Move stock between locations.'],
        'approve_stock_transfers' => ['group' => 'Inventory', 'label' => 'Approve stock transfers', 'description' => 'Approve pending stock transfers.'],
        'complete_stock_transfers' => ['group' => 'Inventory', 'label' => 'Complete stock transfers', 'description' => 'Complete stock transfers.'],
        'adjust_stock' => ['group' => 'Inventory', 'label' => 'Adjust stock', 'description' => 'Record stock adjustments and counts.'],
        'view_stock_movements' => ['group' => 'Inventory', 'label' => 'View stock movements', 'description' => 'Browse the inventory movement ledger.'],
        'manage_stocktakes' => ['group' => 'Inventory', 'label' => 'Manage stocktakes', 'description' => 'Create and count physical stocktakes.'],
        'approve_stocktakes' => ['group' => 'Inventory', 'label' => 'Approve stocktakes', 'description' => 'Approve stocktake variances and apply stock.'],

        // People & Partners
        'manage_customers' => ['group' => 'People & Partners', 'label' => 'Manage customers', 'description' => 'Create and update POS shop customers.'],
        'manage_employees' => ['group' => 'People & Partners', 'label' => 'Manage employees', 'description' => 'Create employee records, photos, and optional system logins.'],
        'manage_suppliers' => ['group' => 'People & Partners', 'label' => 'Manage suppliers', 'description' => 'Create and update procurement suppliers.'],
        'manage_vendors' => ['group' => 'People & Partners', 'label' => 'Manage vendors', 'description' => 'Manage vendors and payouts.'],

        // Procurement
        'manage_purchases' => ['group' => 'Procurement', 'label' => 'Manage purchases', 'description' => 'Receive stock and record supplier purchases.'],
        'manage_purchase_orders' => ['group' => 'Procurement', 'label' => 'Manage purchase orders', 'description' => 'Create and manage purchase orders.'],
        'approve_purchase_orders' => ['group' => 'Procurement', 'label' => 'Approve purchase orders', 'description' => 'Approve purchase orders for receiving.'],

        // Reports & Audit
        'view_pos_reports' => ['group' => 'Reports & Audit', 'label' => 'View reports', 'description' => 'View store sales and commission reports.'],
        'view_cashier_performance' => ['group' => 'Reports & Audit', 'label' => 'View cashier performance', 'description' => 'View cashier sales, refunds, and shift variances.'],
        'view_audit_logs' => ['group' => 'Reports & Audit', 'label' => 'View audit logs', 'description' => 'Browse staff action audit logs.'],
        'backup_database' => ['group' => 'Reports & Audit', 'label' => 'Backup database', 'description' => 'Create and download database backups.'],
        'restore_database' => ['group' => 'Reports & Audit', 'label' => 'Restore database', 'description' => 'Restore the database from a backup file.'],

        // Website
        'manage_blog' => ['group' => 'Website', 'label' => 'Manage blog', 'description' => 'Create and update blog posts.'],
        'manage_reviews' => ['group' => 'Website', 'label' => 'Manage reviews', 'description' => 'Moderate product reviews and Q&A.'],
        'manage_website' => ['group' => 'Website', 'label' => 'Manage website CMS', 'description' => 'Manage enquiries, contacts, newsletter, and other site pages.'],

        // Users
        'view_users' => ['group' => 'Users', 'label' => 'View users', 'description' => 'Browse staff accounts.'],
        'create_users' => ['group' => 'Users', 'label' => 'Create users', 'description' => 'Add staff accounts.'],
        'edit_users' => ['group' => 'Users', 'label' => 'Edit users', 'description' => 'Update staff accounts.'],
        'delete_users' => ['group' => 'Users', 'label' => 'Delete users', 'description' => 'Remove staff accounts.'],
        'assign_roles_to_users' => ['group' => 'Users', 'label' => 'Assign roles to users', 'description' => 'Link roles to staff accounts.'],

        // Access control
        'view_roles' => ['group' => 'Access Control', 'label' => 'View roles', 'description' => 'Browse system roles.'],
        'create_roles' => ['group' => 'Access Control', 'label' => 'Create roles', 'description' => 'Add new roles.'],
        'edit_roles' => ['group' => 'Access Control', 'label' => 'Edit roles', 'description' => 'Update role details.'],
        'delete_roles' => ['group' => 'Access Control', 'label' => 'Delete roles', 'description' => 'Remove roles.'],
        'assign_permissions_to_roles' => ['group' => 'Access Control', 'label' => 'Assign permissions to roles', 'description' => 'Choose which permissions each role has.'],
        'view_permissions' => ['group' => 'Access Control', 'label' => 'View permissions', 'description' => 'Browse the permission catalogue.'],

        // Settings
        'manage_system_settings' => ['group' => 'Settings', 'label' => 'Manage system settings', 'description' => 'Update branding and store settings.'],
        'manage_loyalty' => ['group' => 'Settings', 'label' => 'Manage customer loyalty', 'description' => 'Configure loyalty settings, adjust points, and block cards.'],
        'view_user_groups' => ['group' => 'Settings', 'label' => 'View user groups', 'description' => 'Browse user groups.'],
        'manage_user_groups' => ['group' => 'Settings', 'label' => 'Manage user groups', 'description' => 'Create and update user groups.'],
    ],
];
