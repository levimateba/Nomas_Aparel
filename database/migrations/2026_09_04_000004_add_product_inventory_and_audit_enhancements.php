<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            $productColumns = [
                'reorder_level' => fn (Blueprint $t) => $t->unsignedInteger('reorder_level')->default(0),
                'reorder_quantity' => fn (Blueprint $t) => $t->unsignedInteger('reorder_quantity')->default(0),
                'unit' => fn (Blueprint $t) => $t->string('unit', 50)->default('pcs'),
                'purchase_unit' => fn (Blueprint $t) => $t->string('purchase_unit', 50)->nullable(),
                'purchase_unit_qty' => fn (Blueprint $t) => $t->unsignedInteger('purchase_unit_qty')->default(1),
                'wholesale_price' => fn (Blueprint $t) => $t->decimal('wholesale_price', 12, 2)->nullable(),
                'tax_rate' => fn (Blueprint $t) => $t->decimal('tax_rate', 8, 2)->default(0),
                'product_type' => fn (Blueprint $t) => $t->string('product_type', 20)->default('product')->index(),
                'search_keywords' => fn (Blueprint $t) => $t->text('search_keywords')->nullable(),
                'shelf_location' => fn (Blueprint $t) => $t->string('shelf_location')->nullable()->index(),
                'batch_lot' => fn (Blueprint $t) => $t->string('batch_lot')->nullable()->index(),
                'tracks_expiry' => fn (Blueprint $t) => $t->boolean('tracks_expiry')->default(false),
                'expiry_date' => fn (Blueprint $t) => $t->date('expiry_date')->nullable()->index(),
                'has_product_options' => fn (Blueprint $t) => $t->boolean('has_product_options')->default(false),
                'product_options' => fn (Blueprint $t) => $t->json('product_options')->nullable(),
            ];

            foreach ($productColumns as $name => $add) {
                if (! Schema::hasColumn('products', $name)) {
                    Schema::table('products', function (Blueprint $table) use ($add) {
                        $add($table);
                    });
                }
            }
        }

        if (! Schema::hasTable('product_barcodes')) {
            Schema::create('product_barcodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('barcode')->unique();
                $table->timestamps();
                $table->index(['product_id', 'barcode']);
            });
        }

        if (Schema::hasTable('audit_logs') && ! Schema::hasColumn('audit_logs', 'target_label')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('target_label')->nullable()->after('action');
            });
        }

        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'audit_trail_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('audit_trail_enabled')->default(true);
                $table->string('audit_trail_disabled_by_name')->nullable();
                $table->timestamp('audit_trail_disabled_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');

        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'target_label')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('target_label');
            });
        }

        if (Schema::hasTable('settings')) {
            foreach (['audit_trail_enabled', 'audit_trail_disabled_by_name', 'audit_trail_disabled_at'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    Schema::table('settings', function (Blueprint $table) use ($col) {
                        $table->dropColumn($col);
                    });
                }
            }
        }

        if (Schema::hasTable('products')) {
            $drop = [
                'reorder_level', 'reorder_quantity', 'unit', 'purchase_unit', 'purchase_unit_qty',
                'wholesale_price', 'tax_rate', 'product_type', 'search_keywords', 'shelf_location',
                'batch_lot', 'tracks_expiry', 'expiry_date', 'has_product_options', 'product_options',
            ];
            foreach ($drop as $col) {
                if (Schema::hasColumn('products', $col)) {
                    Schema::table('products', function (Blueprint $table) use ($col) {
                        $table->dropColumn($col);
                    });
                }
            }
        }
    }
};
