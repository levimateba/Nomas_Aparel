<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->nullable()->index();
            $table->string('phone_number', 20);
            $table->decimal('amount', 12, 2);
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('mpesa_receipt_number')->nullable()->index();
            $table->string('transaction_date', 32)->nullable();
            $table->string('result_code', 16)->nullable();
            $table->string('result_description')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('account_reference', 32)->nullable()->index();
            $table->string('transaction_description', 64)->nullable();
            $table->json('checkout_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable()->index();
            $table->timestamps();

            if (Schema::hasTable('orders')) {
                $table->foreign('sale_id')->references('id')->on('orders')->nullOnDelete();
            }
            if (Schema::hasTable('users')) {
                $table->foreign('initiated_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
