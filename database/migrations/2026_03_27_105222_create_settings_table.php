<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Elgon Tech');
            $table->string('site_tagline')->default('ICT Consultancy');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('primary_color')->default('#16456e');
            $table->string('secondary_color')->default('#165752');
            $table->string('tertiary_color')->default('#573c16');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
