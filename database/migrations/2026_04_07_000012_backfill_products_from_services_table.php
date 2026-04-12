<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('services') || !Schema::hasTable('categories')) {
            return;
        }

        if (DB::table('products')->count() > 0) {
            return;
        }

        $services = DB::table('services')->orderBy('id')->get();
        if ($services->isEmpty()) {
            return;
        }

        foreach ($services as $service) {
            $categoryId = null;
            $categoryName = trim((string) ($service->category ?? 'General'));

            if ($categoryName !== '') {
                $category = DB::table('categories')->where('name', $categoryName)->first();
                if (!$category) {
                    $categoryId = DB::table('categories')->insertGetId([
                        'name' => $categoryName,
                        'slug' => Str::slug($categoryName),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $categoryId = $category->id;
                }
            }

            $name = (string) $service->title;
            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $counter = 2;
            while (DB::table('products')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            DB::table('products')->insert([
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'sku' => null,
                'description' => $service->description,
                'price' => 1999.00,
                'sale_price' => null,
                'stock' => 20,
                'image_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally left blank to avoid destructive rollback of user-managed records.
    }
};
