<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'pos_sell_from_all_locations')) {
                $table->boolean('pos_sell_from_all_locations')->default(false)->after('pos_location_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'pos_sell_from_all_locations')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('pos_sell_from_all_locations');
        });
    }
};
