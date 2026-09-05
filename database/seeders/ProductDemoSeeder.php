<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('categories')) {
            return;
        }

        $vendorId = null;
        if (Schema::hasTable('vendors')) {
            $vendor = Vendor::query()->firstOrCreate(
                ['slug' => 'nomas-apparel'],
                [
                    'name' => 'Nomas Apparel',
                    'email' => 'shop@nomasapparel.test',
                    'phone' => '+254 700 000 000',
                    'commission_rate' => 8.00,
                    'is_active' => true,
                    'description' => 'Official in-house clothing collection for suits, uniforms, bags and shoes.',
                ]
            );
            $vendorId = $vendor->id;
        }

        $categoryIds = [];
        foreach (['Suits', 'Clothes', 'Bags', 'Uniforms', 'Shoes'] as $name) {
            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
            $categoryIds[$name] = $category->id;
        }

        $products = [
            [
                'category' => 'Suits',
                'name' => 'Classic Navy Two-Piece Suit',
                'sku' => 'NOM-SUIT-001',
                'description' => 'Tailored navy two-piece suit in a slim fit. Ideal for office, weddings and formal events. Includes jacket and trousers.',
                'price' => 28500,
                'sale_price' => 24900,
                'stock' => 18,
                'image_url' => 'https://images.unsplash.com/photo-1594938291221-94f18cbb0c05?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Suits',
                'name' => 'Charcoal Business Suit',
                'sku' => 'NOM-SUIT-002',
                'description' => 'Charcoal grey business suit with a structured shoulder and comfortable stretch lining. Ready for boardroom and church.',
                'price' => 26500,
                'sale_price' => null,
                'stock' => 12,
                'image_url' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Suits',
                'name' => 'Black Tuxedo Evening Suit',
                'sku' => 'NOM-SUIT-003',
                'description' => 'Black evening tuxedo with satin lapel. Pair with a white shirt and bow tie for gala nights and weddings.',
                'price' => 42000,
                'sale_price' => 38900,
                'stock' => 8,
                'image_url' => 'https://images.unsplash.com/photo-1617137968427-85924c800a22?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Clothes',
                'name' => 'White Oxford Dress Shirt',
                'sku' => 'NOM-CLT-001',
                'description' => 'Crisp cotton Oxford shirt with a regular collar. Easy to pair with suits, uniforms and casual trousers.',
                'price' => 3500,
                'sale_price' => 2900,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Clothes',
                'name' => 'Casual Crew Neck T-Shirt',
                'sku' => 'NOM-CLT-002',
                'description' => 'Soft everyday cotton tee with a clean crew neck. Available as a demo staple for casual wear.',
                'price' => 1800,
                'sale_price' => null,
                'stock' => 55,
                'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Clothes',
                'name' => 'Wool Blend Overcoat',
                'sku' => 'NOM-CLT-003',
                'description' => 'Warm wool-blend overcoat with a tailored silhouette. A smart layer over suits and office outfits.',
                'price' => 14500,
                'sale_price' => 12900,
                'stock' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Clothes',
                'name' => 'Slim Fit Chino Trousers',
                'sku' => 'NOM-CLT-004',
                'description' => 'Versatile slim-fit chinos for work and weekend. Mid-rise with a clean tapered leg.',
                'price' => 4200,
                'sale_price' => null,
                'stock' => 28,
                'image_url' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Bags',
                'name' => 'Leather Laptop Messenger Bag',
                'sku' => 'NOM-BAG-001',
                'description' => 'Genuine-look leather messenger with a padded laptop sleeve. Perfect for office, travel and school.',
                'price' => 8900,
                'sale_price' => 7500,
                'stock' => 16,
                'image_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Bags',
                'name' => 'Structured Office Handbag',
                'sku' => 'NOM-BAG-002',
                'description' => 'Structured everyday handbag with gold-tone hardware. Fits a tablet, wallet and work essentials.',
                'price' => 7200,
                'sale_price' => null,
                'stock' => 14,
                'image_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Bags',
                'name' => 'Weekend Duffel Travel Bag',
                'sku' => 'NOM-BAG-003',
                'description' => 'Roomy duffel for gym, travel and overnight trips. Durable fabric with a wide shoulder strap.',
                'price' => 5600,
                'sale_price' => 4900,
                'stock' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1590874103328-eac38a941954?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Uniforms',
                'name' => 'School Unisex Polo Uniform',
                'sku' => 'NOM-UNI-001',
                'description' => 'Durable school polo uniform top with a knitted collar. Easy-care fabric for daily wear.',
                'price' => 2200,
                'sale_price' => null,
                'stock' => 60,
                'image_url' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a090?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Uniforms',
                'name' => 'Corporate Office Uniform Shirt',
                'sku' => 'NOM-UNI-002',
                'description' => 'Branded-ready corporate shirt for reception, sales and admin teams. Wrinkle-resistant cotton blend.',
                'price' => 2800,
                'sale_price' => 2400,
                'stock' => 35,
                'image_url' => 'https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Uniforms',
                'name' => 'Security Guard Uniform Set',
                'sku' => 'NOM-UNI-003',
                'description' => 'Practical security uniform set with a smart collar and reinforced stitching. Built for long shifts.',
                'price' => 6500,
                'sale_price' => null,
                'stock' => 22,
                'image_url' => 'https://images.unsplash.com/photo-1586363104862-3a5e2af23d95?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Uniforms',
                'name' => 'Chef Kitchen Uniform Jacket',
                'sku' => 'NOM-UNI-004',
                'description' => 'Breathable chef jacket with double-breasted buttons. Suitable for hotels, restaurants and catering teams.',
                'price' => 3900,
                'sale_price' => 3400,
                'stock' => 18,
                'image_url' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Shoes',
                'name' => 'Classic Leather Oxford Shoes',
                'sku' => 'NOM-SHO-001',
                'description' => 'Polished leather Oxfords for suits and office wear. Cushioned insole for all-day comfort.',
                'price' => 7800,
                'sale_price' => 6900,
                'stock' => 24,
                'image_url' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Shoes',
                'name' => 'Everyday Leather Sneakers',
                'sku' => 'NOM-SHO-002',
                'description' => 'Clean white sneakers for casual outfits and travel. Lightweight sole with a durable upper.',
                'price' => 5400,
                'sale_price' => null,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Shoes',
                'name' => 'Running Sport Trainers',
                'sku' => 'NOM-SHO-003',
                'description' => 'Cushioned trainers for gym, running and everyday errands. Breathable mesh with a grippy outsole.',
                'price' => 6200,
                'sale_price' => 5400,
                'stock' => 26,
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Shoes',
                'name' => 'Formal Loafers',
                'sku' => 'NOM-SHO-004',
                'description' => 'Slip-on formal loafers that work with suits, chinos and smart-casual uniforms.',
                'price' => 6700,
                'sale_price' => null,
                'stock' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1533867617558-4213e08d40a0?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        foreach ($products as $item) {
            $localImage = '/images/products/'.Str::slug($item['sku']).'.jpg';
            $localPath = public_path(ltrim($localImage, '/'));
            $imageUrl = is_file($localPath) ? $localImage : $item['image_url'];

            Product::query()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $categoryIds[$item['category']] ?? null,
                    'vendor_id' => $vendorId,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'stock' => $item['stock'],
                    'image_url' => $imageUrl,
                    'is_active' => true,
                ]
            );
        }

        // Keep every catalog product sellable on the storefront.
        Product::query()->update(['is_active' => true]);
    }
}
