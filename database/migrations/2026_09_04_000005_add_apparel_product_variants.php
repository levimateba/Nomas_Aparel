<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_styles')) {
            Schema::create('product_styles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('variant_attributes')) {
            Schema::create('variant_attributes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('type', 40)->default('text'); // text, colour, size
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('variant_attribute_values')) {
            Schema::create('variant_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('variant_attribute_id')->constrained('variant_attributes')->cascadeOnDelete();
                $table->string('value');
                $table->string('code', 40)->nullable();
                $table->string('hex_color', 20)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['variant_attribute_id', 'value'], 'variant_attr_value_unique');
            });
        }

        if (Schema::hasTable('products')) {
            $columns = [
                'has_variants' => fn (Blueprint $t) => $t->boolean('has_variants')->default(false)->after('product_type'),
                'target_audience' => fn (Blueprint $t) => $t->string('target_audience', 40)->nullable()->index(),
                'product_style_id' => fn (Blueprint $t) => $t->foreignId('product_style_id')->nullable()->constrained('product_styles')->nullOnDelete(),
                'care_instructions' => fn (Blueprint $t) => $t->text('care_instructions')->nullable(),
                'weight' => fn (Blueprint $t) => $t->decimal('weight', 10, 3)->nullable(),
                'length' => fn (Blueprint $t) => $t->decimal('length', 10, 2)->nullable(),
                'width' => fn (Blueprint $t) => $t->decimal('width', 10, 2)->nullable(),
                'height' => fn (Blueprint $t) => $t->decimal('height', 10, 2)->nullable(),
                'requires_shipping' => fn (Blueprint $t) => $t->boolean('requires_shipping')->default(true),
                'shipping_class' => fn (Blueprint $t) => $t->string('shipping_class', 80)->nullable(),
                'free_shipping' => fn (Blueprint $t) => $t->boolean('free_shipping')->default(false),
                'store_visibility' => fn (Blueprint $t) => $t->string('store_visibility', 20)->default('visible'),
                'is_featured' => fn (Blueprint $t) => $t->boolean('is_featured')->default(false),
                'is_new_arrival' => fn (Blueprint $t) => $t->boolean('is_new_arrival')->default(false),
                'is_best_seller' => fn (Blueprint $t) => $t->boolean('is_best_seller')->default(false),
                'allow_online_purchase' => fn (Blueprint $t) => $t->boolean('allow_online_purchase')->default(true),
                'display_stock' => fn (Blueprint $t) => $t->boolean('display_stock')->default(true),
                'allow_backorders' => fn (Blueprint $t) => $t->boolean('allow_backorders')->default(false),
                'meta_title' => fn (Blueprint $t) => $t->string('meta_title')->nullable(),
                'meta_description' => fn (Blueprint $t) => $t->text('meta_description')->nullable(),
                'meta_keywords' => fn (Blueprint $t) => $t->string('meta_keywords')->nullable(),
                'seo_noindex' => fn (Blueprint $t) => $t->boolean('seo_noindex')->default(false),
            ];

            foreach ($columns as $name => $add) {
                if (! Schema::hasColumn('products', $name)) {
                    Schema::table('products', function (Blueprint $table) use ($add) {
                        $add($table);
                    });
                }
            }
        }

        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('sku')->nullable()->unique();
                $table->string('barcode', 64)->nullable()->unique();
                $table->decimal('buying_price', 12, 2)->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->decimal('wholesale_price', 12, 2)->nullable();
                $table->decimal('sale_price', 12, 2)->nullable();
                $table->decimal('tax_rate', 8, 2)->nullable();
                $table->integer('stock')->default(0);
                $table->unsignedInteger('reorder_level')->default(0);
                $table->unsignedInteger('reorder_quantity')->default(0);
                $table->string('shelf_location')->nullable();
                $table->decimal('weight', 10, 3)->nullable();
                $table->string('image_url')->nullable();
                $table->string('option_signature')->nullable()->index();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'option_signature']);
            });
        }

        if (! Schema::hasTable('product_variant_attribute_values')) {
            Schema::create('product_variant_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->foreignId('variant_attribute_id')->constrained('variant_attributes')->cascadeOnDelete();
                // Short FK name — MySQL limit is 64 chars
                $table->unsignedBigInteger('variant_attribute_value_id');
                $table->foreign('variant_attribute_value_id', 'pvav_attr_value_fk')
                    ->references('id')
                    ->on('variant_attribute_values')
                    ->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['product_variant_id', 'variant_attribute_id'], 'pv_attr_unique');
            });
        }

        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('image_url');
                $table->string('alt_text')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_specifications')) {
            Schema::create('product_specifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('value');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('order_items', 'product_variant_id')) {
                    $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
                }
                if (! Schema::hasColumn('order_items', 'variant_name')) {
                    $table->string('variant_name')->nullable()->after('product_name');
                }
                if (! Schema::hasColumn('order_items', 'sku')) {
                    $table->string('sku')->nullable()->after('variant_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                foreach (['product_variant_id', 'variant_name', 'sku'] as $col) {
                    if (Schema::hasColumn('order_items', $col)) {
                        if ($col === 'product_variant_id') {
                            $table->dropConstrainedForeignId('product_variant_id');
                        } else {
                            $table->dropColumn($col);
                        }
                    }
                }
            });
        }

        Schema::dropIfExists('product_specifications');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');

        if (Schema::hasTable('products')) {
            $drop = [
                'has_variants', 'target_audience', 'product_style_id', 'care_instructions',
                'weight', 'length', 'width', 'height', 'requires_shipping', 'shipping_class', 'free_shipping',
                'store_visibility', 'is_featured', 'is_new_arrival', 'is_best_seller',
                'allow_online_purchase', 'display_stock', 'allow_backorders',
                'meta_title', 'meta_description', 'meta_keywords', 'seo_noindex',
            ];
            foreach ($drop as $col) {
                if (Schema::hasColumn('products', $col)) {
                    Schema::table('products', function (Blueprint $table) use ($col) {
                        if ($col === 'product_style_id') {
                            $table->dropConstrainedForeignId('product_style_id');
                        } else {
                            $table->dropColumn($col);
                        }
                    });
                }
            }
        }

        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('product_styles');
    }
};
