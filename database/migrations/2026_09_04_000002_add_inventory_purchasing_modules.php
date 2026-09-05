<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_brands')) {
            Schema::create('product_brands', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('short_description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('company_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->string('tax_pin')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'brand_id')) {
                    $table->foreignId('brand_id')->nullable()->after('vendor_id')->constrained('product_brands')->nullOnDelete();
                }
                if (! Schema::hasColumn('products', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->after('brand_id')->constrained('suppliers')->nullOnDelete();
                }
                if (! Schema::hasColumn('products', 'buying_price')) {
                    $table->decimal('buying_price', 12, 2)->nullable()->after('price');
                }
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 40);
                $table->integer('quantity');
                $table->integer('stock_before');
                $table->integer('stock_after');
                $table->string('reason')->nullable();
                $table->nullableMorphs('reference');
                $table->timestamps();
                $table->index(['product_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number')->unique();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('purchase_order_id')->nullable();
                $table->date('purchase_date');
                $table->date('due_date')->nullable();
                $table->string('invoice_reference')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->decimal('balance_due', 12, 2)->default(0);
                $table->string('payment_status', 20)->default('unpaid');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->integer('quantity');
                $table->decimal('buying_price', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->decimal('total', 12, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_payments')) {
            Schema::create('purchase_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('payment_method', 40)->default('Cash');
                $table->string('reference')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->date('order_date');
                $table->date('expected_date')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->string('status', 40)->default('draft');
                $table->text('notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'purchase_order_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                // Ensure FK after purchase_orders exists (SQLite may already have the column without FK).
                try {
                    $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
                } catch (\Throwable) {
                    // Ignore if already constrained or driver does not support alter.
                }
            });
        }

        if (! Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->integer('ordered_qty');
                $table->integer('received_qty')->default(0);
                $table->decimal('buying_price', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->decimal('total', 12, 2);
                $table->timestamps();
                $table->unique(['purchase_order_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_items');

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'purchase_order_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                try {
                    $table->dropForeign(['purchase_order_id']);
                } catch (\Throwable) {
                }
            });
        }

        Schema::dropIfExists('purchases');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_movements');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'supplier_id')) {
                    $table->dropConstrainedForeignId('supplier_id');
                }
                if (Schema::hasColumn('products', 'brand_id')) {
                    $table->dropConstrainedForeignId('brand_id');
                }
                if (Schema::hasColumn('products', 'buying_price')) {
                    $table->dropColumn('buying_price');
                }
            });
        }

        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('product_brands');
    }
};
