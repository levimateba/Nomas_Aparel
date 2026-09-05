<?php

namespace Database\Seeders;

use App\Models\ProductStyle;
use App\Models\VariantAttribute;
use App\Models\VariantAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApparelCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            'T-Shirt', 'Shirt', 'Polo Shirt', 'Blouse', 'Dress', 'Skirt', 'Trousers', 'Jeans',
            'Shorts', 'Jacket', 'Coat', 'Hoodie', 'Sweater', 'Tracksuit', 'Suit', 'Blazer',
            'Uniform', 'Shoes', 'Sneakers', 'Sandals', 'Boots', 'Bag', 'Belt', 'Cap', 'Other',
        ];
        foreach ($styles as $i => $name) {
            ProductStyle::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }

        $catalog = [
            'Size' => [
                'type' => 'size',
                'values' => [
                    ['XS', 'XS'], ['S', 'S'], ['M', 'M'], ['L', 'L'], ['XL', 'XL'], ['XXL', 'XXL'], ['XXXL', 'XXXL'],
                    ['28', '28'], ['30', '30'], ['32', '32'], ['34', '34'], ['36', '36'], ['38', '38'], ['40', '40'], ['42', '42'],
                    ['36EU', '36'], ['37EU', '37'], ['38EU', '38'], ['39EU', '39'], ['40EU', '40'], ['41EU', '41'],
                    ['42EU', '42'], ['43EU', '43'], ['44EU', '44'], ['45EU', '45'],
                ],
            ],
            'Colour' => [
                'type' => 'colour',
                'values' => [
                    ['Black', 'BLK', '#111111'], ['White', 'WHT', '#FFFFFF'], ['Navy', 'NVY', '#001F3F'],
                    ['Red', 'RED', '#C41E3A'], ['Blue', 'BLU', '#2563EB'], ['Green', 'GRN', '#16A34A'],
                    ['Grey', 'GRY', '#6B7280'], ['Brown', 'BRN', '#92400E'], ['Beige', 'BEG', '#D6C3A8'],
                    ['Pink', 'PNK', '#EC4899'], ['Other', 'OTH', null],
                ],
            ],
            'Material' => [
                'type' => 'text',
                'values' => [
                    ['Cotton', 'CTN'], ['Polyester', 'PLY'], ['Denim', 'DNM'], ['Linen', 'LIN'],
                    ['Wool', 'WOL'], ['Leather', 'LTH'], ['Silk', 'SLK'], ['Nylon', 'NYL'], ['Cotton Blend', 'CBL'],
                ],
            ],
            'Fit' => [
                'type' => 'text',
                'values' => [
                    ['Regular', 'REG'], ['Slim', 'SLM'], ['Relaxed', 'RLX'], ['Oversized', 'OVR'],
                    ['Skinny', 'SKN'], ['Straight', 'STR'], ['Loose', 'LOS'], ['Other', 'OTH'],
                ],
            ],
        ];

        $sortAttr = 1;
        foreach ($catalog as $attrName => $meta) {
            $attr = VariantAttribute::query()->updateOrCreate(
                ['slug' => Str::slug($attrName)],
                ['name' => $attrName, 'type' => $meta['type'], 'sort_order' => $sortAttr++, 'is_active' => true]
            );

            foreach ($meta['values'] as $i => $row) {
                $value = $row[0];
                $code = $row[1] ?? null;
                $hex = $row[2] ?? null;
                VariantAttributeValue::query()->updateOrCreate(
                    [
                        'variant_attribute_id' => $attr->id,
                        'value' => $value,
                    ],
                    [
                        'code' => $code,
                        'hex_color' => $hex,
                        'sort_order' => $i + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
