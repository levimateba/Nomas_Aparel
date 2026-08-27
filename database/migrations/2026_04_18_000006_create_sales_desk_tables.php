<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cashier_shifts')) {
            Schema::create('cashier_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('opening_cash', 12, 2)->default(0);
                $table->decimal('expected_cash', 12, 2)->nullable();
                $table->decimal('actual_cash', 12, 2)->nullable();
                $table->decimal('difference', 12, 2)->nullable();
                $table->timestamp('opened_at');
                $table->timestamp('closed_at')->nullable();
                $table->string('status', 20)->default('open');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'cashier_shift_id')) {
                $table->foreignId('cashier_shift_id')->nullable()->after('user_id')->constrained('cashier_shifts')->nullOnDelete();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'returned_quantity')) {
                $table->unsignedInteger('returned_quantity')->default(0)->after('quantity');
            }
        });

        if (! Schema::hasTable('order_returns')) {
            Schema::create('order_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number')->unique();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('total', 12, 2)->default(0);
                $table->string('reason')->nullable();
                $table->string('refund_method', 40)->default('original');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_return_items')) {
            Schema::create('order_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_return_id')->constrained('order_returns')->cascadeOnDelete();
                $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('total', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_return_items');
        Schema::dropIfExists('order_returns');

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'returned_quantity')) {
                $table->dropColumn('returned_quantity');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'cashier_shift_id')) {
                $table->dropConstrainedForeignId('cashier_shift_id');
            }
        });

        Schema::dropIfExists('cashier_shifts');
    }
};
