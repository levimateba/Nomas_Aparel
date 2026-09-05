<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'return_requested_at')) {
                $table->timestamp('return_requested_at')->nullable()->after('cancellation_reason');
            }
            if (! Schema::hasColumn('orders', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('return_requested_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'return_reason')) {
                $table->dropColumn('return_reason');
            }
            if (Schema::hasColumn('orders', 'return_requested_at')) {
                $table->dropColumn('return_requested_at');
            }
        });
    }
};
