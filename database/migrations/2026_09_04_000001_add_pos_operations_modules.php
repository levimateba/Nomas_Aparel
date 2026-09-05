<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shop_customers')) {
            Schema::create('shop_customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable()->index();
                $table->string('email')->nullable()->index();
                $table->string('address')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('balance', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description')->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('payment_method', 40)->default('Cash');
                $table->date('expense_date');
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('module')->nullable();
                $table->text('description')->nullable();
                $table->nullableMorphs('subject');
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
                $table->index(['module', 'created_at']);
            });
        }

        if (! Schema::hasTable('held_sales')) {
            Schema::create('held_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('label')->nullable();
                $table->json('payload');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'shop_customer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('shop_customer_id')->nullable()->after('user_id')->constrained('shop_customers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'shop_customer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shop_customer_id');
            });
        }

        Schema::dropIfExists('held_sales');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('shop_customers');
    }
};
