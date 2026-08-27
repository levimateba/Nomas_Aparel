<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_takes', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_takes', 'stocktake_date')) {
                $table->date('stocktake_date')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('stock_takes', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('stock_takes', 'filter_stock_status')) {
                $table->string('filter_stock_status', 20)->nullable()->after('vendor_id');
            }
            if (! Schema::hasColumn('stock_takes', 'positive_variance_value')) {
                $table->decimal('positive_variance_value', 12, 2)->default(0)->after('completed_at');
            }
            if (! Schema::hasColumn('stock_takes', 'negative_variance_value')) {
                $table->decimal('negative_variance_value', 12, 2)->default(0)->after('positive_variance_value');
            }
        });

        Schema::table('stock_take_items', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_take_items', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->default(0)->after('counted_qty');
            }
            if (! Schema::hasColumn('stock_take_items', 'variance')) {
                $table->integer('variance')->default(0)->after('unit_cost');
            }
            if (! Schema::hasColumn('stock_take_items', 'variance_value')) {
                $table->decimal('variance_value', 12, 2)->default(0)->after('variance');
            }
            if (! Schema::hasColumn('stock_take_items', 'reason')) {
                $table->string('reason')->nullable()->after('variance_value');
            }
        });

        DB::table('stock_takes')->where('status', 'in_progress')->update(['status' => 'counting']);
        DB::table('stock_takes')->where('status', 'completed')->update(['status' => 'approved']);
        DB::table('stock_takes')->whereNull('stocktake_date')->update(['stocktake_date' => now()->toDateString()]);
    }

    public function down(): void
    {
        Schema::table('stock_takes', function (Blueprint $table) {
            if (Schema::hasColumn('stock_takes', 'vendor_id')) {
                $table->dropConstrainedForeignId('vendor_id');
            }
            foreach (['stocktake_date', 'filter_stock_status', 'positive_variance_value', 'negative_variance_value'] as $column) {
                if (Schema::hasColumn('stock_takes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('stock_take_items', function (Blueprint $table) {
            foreach (['unit_cost', 'variance', 'variance_value', 'reason'] as $column) {
                if (Schema::hasColumn('stock_take_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
