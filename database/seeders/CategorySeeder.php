<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        $categories = [
            'Supermarket',
            'Phones',
            'Computing',
            'Fashion',
            'Electronics',
            'Suits',
            'Clothes',
            'Bags',
            'Uniforms',
            'Shoes',
        ];

        foreach ($categories as $name) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
        }
    }
}
