<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_locations')) {
            Schema::create('stock_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 40)->unique();
                $table->string('type', 40)->default('other');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inventory_stocks')) {
            Schema::create('inventory_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                // 0 = product-level (no variant). Avoids MySQL NULL unique gaps.
                $table->unsignedBigInteger('product_variant_id')->default(0);
                $table->foreignId('stock_location_id')->constrained('stock_locations')->restrictOnDelete();
                $table->integer('quantity')->default(0);
                $table->integer('reorder_level')->default(0);
                $table->integer('reorder_quantity')->default(0);
                $table->timestamps();

                $table->unique(
                    ['product_id', 'product_variant_id', 'stock_location_id'],
                    'inventory_stocks_product_variant_location_unique'
                );
                $table->index(['stock_location_id', 'quantity']);
                $table->index(['product_id', 'stock_location_id']);
            });
        }

        if (! Schema::hasTable('stock_transfers')) {
            Schema::create('stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_number')->unique();
                $table->foreignId('from_location_id')->constrained('stock_locations')->restrictOnDelete();
                $table->foreignId('to_location_id')->constrained('stock_locations')->restrictOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 20)->default('draft');
                $table->string('reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('stock_transfer_items')) {
            Schema::create('stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();

                $table->index(['product_id', 'product_variant_id']);
            });
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (! Schema::hasColumn('stock_movements', 'product_variant_id')) {
                    $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
                }
                if (! Schema::hasColumn('stock_movements', 'from_location_id')) {
                    $table->foreignId('from_location_id')->nullable()->after('product_variant_id')->constrained('stock_locations')->nullOnDelete();
                }
                if (! Schema::hasColumn('stock_movements', 'to_location_id')) {
                    $table->foreignId('to_location_id')->nullable()->after('from_location_id')->constrained('stock_locations')->nullOnDelete();
                }
                if (! Schema::hasColumn('stock_movements', 'stock_location_id')) {
                    $table->foreignId('stock_location_id')->nullable()->after('to_location_id')->constrained('stock_locations')->nullOnDelete();
                }
                if (! Schema::hasColumn('stock_movements', 'notes')) {
                    $table->text('notes')->nullable()->after('reason');
                }
                $table->index(['type', 'created_at']);
                $table->index(['from_location_id', 'to_location_id']);
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (! Schema::hasColumn('settings', 'pos_location_id')) {
                    $table->foreignId('pos_location_id')->nullable()->after('allow_negative_stock')->constrained('stock_locations')->nullOnDelete();
                }
                if (! Schema::hasColumn('settings', 'online_sales_location_id')) {
                    $table->foreignId('online_sales_location_id')->nullable()->after('pos_location_id')->constrained('stock_locations')->nullOnDelete();
                }
                if (! Schema::hasColumn('settings', 'online_sales_stock_mode')) {
                    $table->string('online_sales_stock_mode', 20)->default('single')->after('online_sales_location_id');
                }
            });
        }

        if (Schema::hasTable('purchases') && ! Schema::hasColumn('purchases', 'stock_location_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('stock_location_id')->nullable()->after('supplier_id')->constrained('stock_locations')->nullOnDelete();
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'stock_location_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('stock_location_id')->nullable()->after('source')->constrained('stock_locations')->nullOnDelete();
            });
        }

        $this->seedDefaultLocationsAndMigrateStock();
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'stock_location_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('stock_location_id');
            });
        }
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'stock_location_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropConstrainedForeignId('stock_location_id');
            });
        }
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'online_sales_stock_mode')) {
                    $table->dropColumn('online_sales_stock_mode');
                }
                if (Schema::hasColumn('settings', 'online_sales_location_id')) {
                    $table->dropConstrainedForeignId('online_sales_location_id');
                }
                if (Schema::hasColumn('settings', 'pos_location_id')) {
                    $table->dropConstrainedForeignId('pos_location_id');
                }
            });
        }

        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('stock_locations');
    }

    private function seedDefaultLocationsAndMigrateStock(): void
    {
        $now = now();

        $storeId = DB::table('stock_locations')->where('code', 'STORE')->value('id');
        if (! $storeId) {
            $storeId = DB::table('stock_locations')->insertGetId([
                'name' => 'Main Store',
                'code' => 'STORE',
                'type' => 'store',
                'description' => 'Stockroom / back-store inventory',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $shopId = DB::table('stock_locations')->where('code', 'SHOP')->value('id');
        if (! $shopId) {
            $shopId = DB::table('stock_locations')->insertGetId([
                'name' => 'Shop Floor',
                'code' => 'SHOP',
                'type' => 'shop',
                'description' => 'Retail shop / display area',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('settings')) {
            $settingsRow = DB::table('settings')->orderBy('id')->first();
            if ($settingsRow) {
                $update = ['updated_at' => $now];
                if (Schema::hasColumn('settings', 'pos_location_id') && empty($settingsRow->pos_location_id)) {
                    $update['pos_location_id'] = $shopId;
                }
                if (Schema::hasColumn('settings', 'online_sales_location_id') && empty($settingsRow->online_sales_location_id)) {
                    $update['online_sales_location_id'] = $storeId;
                }
                if (Schema::hasColumn('settings', 'online_sales_stock_mode') && empty($settingsRow->online_sales_stock_mode)) {
                    $update['online_sales_stock_mode'] = 'single';
                }
                DB::table('settings')->where('id', $settingsRow->id)->update($update);
            }
        }

        if (! Schema::hasTable('inventory_stocks') || ! Schema::hasTable('products')) {
            return;
        }

        $existing = (int) DB::table('inventory_stocks')->count();
        if ($existing > 0) {
            return;
        }

        if (Schema::hasTable('product_variants')) {
            $variants = DB::table('product_variants')->select('id', 'product_id', 'stock', 'reorder_level', 'reorder_quantity')->get();
            foreach ($variants as $variant) {
                $qty = max(0, (int) $variant->stock);
                DB::table('inventory_stocks')->insert([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => (int) $variant->id,
                    'stock_location_id' => $storeId,
                    'quantity' => $qty,
                    'reorder_level' => max(0, (int) ($variant->reorder_level ?? 0)),
                    'reorder_quantity' => max(0, (int) ($variant->reorder_quantity ?? 0)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('inventory_stocks')->insert([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => (int) $variant->id,
                    'stock_location_id' => $shopId,
                    'quantity' => 0,
                    'reorder_level' => 0,
                    'reorder_quantity' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $variantProductIds = Schema::hasTable('product_variants')
            ? DB::table('product_variants')->where('is_active', true)->distinct()->pluck('product_id')->all()
            : [];

        $products = DB::table('products')->select('id', 'stock', 'reorder_level', 'reorder_quantity', 'has_variants')->get();
        foreach ($products as $product) {
            if (! empty($product->has_variants) || in_array($product->id, $variantProductIds, true)) {
                continue;
            }

            $qty = max(0, (int) $product->stock);
            DB::table('inventory_stocks')->insert([
                'product_id' => $product->id,
                'product_variant_id' => 0,
                'stock_location_id' => $storeId,
                'quantity' => $qty,
                'reorder_level' => max(0, (int) ($product->reorder_level ?? 0)),
                'reorder_quantity' => max(0, (int) ($product->reorder_quantity ?? 0)),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('inventory_stocks')->insert([
                'product_id' => $product->id,
                'product_variant_id' => 0,
                'stock_location_id' => $shopId,
                'quantity' => 0,
                'reorder_level' => 0,
                'reorder_quantity' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
