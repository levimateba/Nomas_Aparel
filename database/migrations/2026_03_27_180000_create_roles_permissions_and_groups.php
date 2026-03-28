<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('is_admin')->constrained('roles')->nullOnDelete();
            $table->foreignId('user_group_id')->nullable()->after('role_id')->constrained('user_groups')->nullOnDelete();
        });

        $now = now();

        DB::table('roles')->insert([
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full administrative access.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Internal team members with operational access.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Clients', 'slug' => 'clients', 'description' => 'Client accounts with limited access.', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('permissions')->insert([
            ['name' => 'Manage Content', 'slug' => 'manage-content', 'description' => 'Create and edit website content.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'Create and edit user accounts.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'description' => 'Update system settings and branding.', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('user_groups')->insert([
            ['name' => 'Management', 'description' => 'Leadership and account owners.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Operations', 'description' => 'Daily delivery and support users.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Clients', 'description' => 'External client accounts.', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
        $staffRoleId = DB::table('roles')->where('slug', 'staff')->value('id');
        $managementGroupId = DB::table('user_groups')->where('name', 'Management')->value('id');
        $operationsGroupId = DB::table('user_groups')->where('name', 'Operations')->value('id');

        DB::table('users')
            ->where('is_admin', true)
            ->update(['role_id' => $adminRoleId, 'user_group_id' => $managementGroupId]);

        DB::table('users')
            ->where('is_admin', false)
            ->update(['role_id' => $staffRoleId, 'user_group_id' => $operationsGroupId]);

        $roleMap = DB::table('roles')->pluck('id', 'slug');
        $permissionMap = DB::table('permissions')->pluck('id', 'slug');

        DB::table('permission_role')->insert([
            ['role_id' => $roleMap['admin'], 'permission_id' => $permissionMap['manage-content'], 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => $roleMap['admin'], 'permission_id' => $permissionMap['manage-users'], 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => $roleMap['admin'], 'permission_id' => $permissionMap['manage-settings'], 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => $roleMap['staff'], 'permission_id' => $permissionMap['manage-content'], 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropConstrainedForeignId('user_group_id');
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('roles');
    }
};
