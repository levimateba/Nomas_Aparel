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

        $columns = [
            'trading_name' => fn (Blueprint $table) => $table->string('trading_name')->nullable(),
            'business_registration_number' => fn (Blueprint $table) => $table->string('business_registration_number')->nullable(),
            'tax_pin' => fn (Blueprint $table) => $table->string('tax_pin')->nullable(),
            'phone' => fn (Blueprint $table) => $table->string('phone', 80)->nullable(),
            'email' => fn (Blueprint $table) => $table->string('email')->nullable(),
            'city' => fn (Blueprint $table) => $table->string('city')->nullable(),
            'currency' => fn (Blueprint $table) => $table->string('currency', 8)->default('KES'),
            'address' => fn (Blueprint $table) => $table->text('address')->nullable(),
            'receipt_header' => fn (Blueprint $table) => $table->text('receipt_header')->nullable(),
            'receipt_footer' => fn (Blueprint $table) => $table->text('receipt_footer')->nullable(),
            'receipt_print_mode' => fn (Blueprint $table) => $table->string('receipt_print_mode', 40)->default('customer'),
            'auto_open_drawer_cash' => fn (Blueprint $table) => $table->boolean('auto_open_drawer_cash')->default(true),
            'auto_print_receipt' => fn (Blueprint $table) => $table->boolean('auto_print_receipt')->default(false),
            'escpos_enabled' => fn (Blueprint $table) => $table->boolean('escpos_enabled')->default(true),
            'logo_show_on_login' => fn (Blueprint $table) => $table->boolean('logo_show_on_login')->default(false),
            'logo_show_on_sidebar' => fn (Blueprint $table) => $table->boolean('logo_show_on_sidebar')->default(false),
            'logo_show_on_receipts' => fn (Blueprint $table) => $table->boolean('logo_show_on_receipts')->default(false),
            'tax_enabled' => fn (Blueprint $table) => $table->boolean('tax_enabled')->default(false),
            'tax_rate' => fn (Blueprint $table) => $table->decimal('tax_rate', 5, 2)->default(16),
            'tax_inclusive' => fn (Blueprint $table) => $table->boolean('tax_inclusive')->default(true),
            'require_open_shift' => fn (Blueprint $table) => $table->boolean('require_open_shift')->default(false),
            'enforce_credit_limit' => fn (Blueprint $table) => $table->boolean('enforce_credit_limit')->default(true),
            'allow_negative_stock' => fn (Blueprint $table) => $table->boolean('allow_negative_stock')->default(false),
            'system_font_size' => fn (Blueprint $table) => $table->string('system_font_size', 8)->default('14'),
        ];

        foreach ($columns as $name => $add) {
            if (! Schema::hasColumn('settings', $name)) {
                Schema::table('settings', function (Blueprint $table) use ($add) {
                    $add($table);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $drop = array_keys([
            'trading_name' => true,
            'business_registration_number' => true,
            'tax_pin' => true,
            'phone' => true,
            'email' => true,
            'city' => true,
            'currency' => true,
            'address' => true,
            'receipt_header' => true,
            'receipt_footer' => true,
            'receipt_print_mode' => true,
            'auto_open_drawer_cash' => true,
            'auto_print_receipt' => true,
            'escpos_enabled' => true,
            'logo_show_on_login' => true,
            'logo_show_on_sidebar' => true,
            'logo_show_on_receipts' => true,
            'tax_enabled' => true,
            'tax_rate' => true,
            'tax_inclusive' => true,
            'require_open_shift' => true,
            'enforce_credit_limit' => true,
            'allow_negative_stock' => true,
            'system_font_size' => true,
        ]);

        foreach ($drop as $column) {
            if (Schema::hasColumn('settings', $column)) {
                Schema::table('settings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
