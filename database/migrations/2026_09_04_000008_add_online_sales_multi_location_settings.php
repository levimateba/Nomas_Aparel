<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'online_fulfilment_strategy')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('online_fulfilment_strategy', 20)->default('priority')->after('online_sales_stock_mode');
            });
        }

        if (! Schema::hasTable('online_sales_locations')) {
            Schema::create('online_sales_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_location_id')->constrained('stock_locations')->cascadeOnDelete();
                $table->unsignedInteger('priority')->default(1);
                $table->timestamps();
                $table->unique('stock_location_id');
                $table->index('priority');
            });
        }

        if (! Schema::hasTable('order_stock_allocations')) {
            Schema::create('order_stock_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('stock_location_id')->constrained('stock_locations')->restrictOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();

                $table->index(['order_id', 'stock_location_id']);
                $table->index(['product_id', 'product_variant_id']);
                $table->index(['stock_location_id', 'created_at']);
            });
        }

        $this->seedFromExistingSettings();
    }

    public function down(): void
    {
        Schema::dropIfExists('order_stock_allocations');
        Schema::dropIfExists('online_sales_locations');

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'online_fulfilment_strategy')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('online_fulfilment_strategy');
            });
        }
    }

    private function seedFromExistingSettings(): void
    {
        if (! Schema::hasTable('online_sales_locations') || ! Schema::hasTable('settings')) {
            return;
        }

        if (DB::table('online_sales_locations')->count() > 0) {
            return;
        }

        $settings = DB::table('settings')->orderBy('id')->first();
        $locationId = $settings->online_sales_location_id ?? null;

        if (! $locationId) {
            $locationId = DB::table('stock_locations')->where('code', 'STORE')->value('id')
                ?: DB::table('stock_locations')->where('is_active', 1)->orderBy('sort_order')->value('id');
        }

        if ($locationId) {
            $now = now();
            DB::table('online_sales_locations')->insert([
                'stock_location_id' => $locationId,
                'priority' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($settings && Schema::hasColumn('settings', 'online_fulfilment_strategy')) {
            DB::table('settings')->where('id', $settings->id)->update([
                'online_fulfilment_strategy' => $settings->online_fulfilment_strategy ?? 'priority',
                'updated_at' => now(),
            ]);
        }
    }
};
