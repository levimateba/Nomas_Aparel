<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->string('short_description')->nullable()->after('category');
            $table->text('description')->nullable()->after('short_description');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'short_description', 'description']);
        });
    }
};
