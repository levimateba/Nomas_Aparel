<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('image')->nullable()->after('content');
            $table->string('category')->nullable()->after('image');
            $table->string('author')->nullable()->after('category');
            $table->timestamp('published_at')->nullable()->after('published');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['image', 'category', 'author', 'published_at']);
        });
    }
};
