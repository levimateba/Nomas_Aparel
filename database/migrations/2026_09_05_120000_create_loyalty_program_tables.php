<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_settings')) {
            Schema::create('loyalty_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('enabled')->default(false);
                $table->decimal('amount_per_point', 12, 2)->default(100);
                $table->unsignedInteger('points_awarded')->default(1);
                $table->decimal('minimum_purchase', 12, 2)->default(100);
                $table->boolean('redemption_enabled')->default(true);
                $table->unsignedInteger('redemption_points')->default(100);
                $table->decimal('redemption_value', 12, 2)->default(10);
                $table->boolean('allow_earn_on_discounted')->default(true);
                $table->boolean('allow_redemption_at_pos')->default(true);
                $table->boolean('show_estimated_points_on_pos')->default(true);
                $table->boolean('show_balance_after_sale')->default(true);
                $table->boolean('show_on_receipt')->default(true);
                $table->boolean('reverse_points_on_refund')->default(true);
                $table->boolean('points_expiration_enabled')->default(false);
                $table->unsignedInteger('points_expiration_days')->default(365);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('loyalty_cards')) {
            Schema::create('loyalty_cards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_customer_id')->constrained('shop_customers')->cascadeOnDelete();
                $table->string('card_number', 40)->unique();
                $table->unsignedInteger('points_balance')->default(0);
                $table->string('status', 20)->default('active'); // active, blocked, expired
                $table->timestamp('issued_at')->nullable();
                $table->timestamps();
                $table->index(['shop_customer_id', 'status']);
            });
        }

        if (! Schema::hasTable('loyalty_transactions')) {
            Schema::create('loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_customer_id')->constrained('shop_customers')->cascadeOnDelete();
                $table->foreignId('loyalty_card_id')->constrained('loyalty_cards')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('order_return_id')->nullable()->constrained('order_returns')->nullOnDelete();
                $table->string('type', 20); // earned, redeemed, reversed, adjustment, expired
                $table->integer('points'); // signed: +earn, -redeem/reverse
                $table->unsignedInteger('balance_before')->default(0);
                $table->unsignedInteger('balance_after')->default(0);
                $table->string('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['shop_customer_id', 'created_at']);
                $table->index(['type', 'created_at']);
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'loyalty_points_earned')) {
                $table->unsignedInteger('loyalty_points_earned')->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('orders', 'loyalty_points_redeemed')) {
                $table->unsignedInteger('loyalty_points_redeemed')->default(0)->after('loyalty_points_earned');
            }
            if (! Schema::hasColumn('orders', 'loyalty_discount_amount')) {
                $table->decimal('loyalty_discount_amount', 12, 2)->default(0)->after('loyalty_points_redeemed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['loyalty_discount_amount', 'loyalty_points_redeemed', 'loyalty_points_earned'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_cards');
        Schema::dropIfExists('loyalty_settings');
    }
};
