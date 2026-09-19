<?php
declare(strict_types=1);
if (($_GET['token'] ?? '') !== 'nomas-deploy-2026-03-27') {
    http_response_code(403);
    exit('Forbidden');
}
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
header('Content-Type: text/plain; charset=utf-8');

$term = trim((string) ($_GET['q'] ?? 'Fashion Rubber'));
echo "SEARCH: {$term}\n\n";

$products = App\Models\Product::query()
    ->with(['variants:id,product_id,name,sku,stock,price,is_active', 'category:id,name', 'brandRelation'])
    ->where(function ($q) use ($term) {
        $q->where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->orWhere('barcode', 'like', "%{$term}%");
    })
    ->orderByDesc('id')
    ->limit(20)
    ->get();

echo 'matches='.$products->count()."\n\n";
foreach ($products as $p) {
    echo "ID {$p->id}\n";
    echo "  name={$p->name}\n";
    echo '  active='.($p->is_active ? '1' : '0')."\n";
    echo '  has_variants='.($p->has_variants ? '1' : '0')."\n";
    echo "  stock={$p->stock}\n";
    echo "  price={$p->price}\n";
    echo '  store_visibility='.($p->store_visibility ?? '')."\n";
    echo '  category='.($p->category?->name ?? '-')."\n";
    echo "  sku={$p->sku}\n";
    echo "  created={$p->created_at}\n";
    echo '  variants='.$p->variants->count()."\n";
    foreach ($p->variants as $v) {
        echo "    - {$v->name} sku={$v->sku} stock={$v->stock} price={$v->price} active=".($v->is_active ? '1' : '0')."\n";
    }
    echo "\n";
}

echo "--- latest 10 products ---\n";
foreach (App\Models\Product::orderByDesc('id')->limit(10)->get(['id', 'name', 'is_active', 'stock', 'has_variants', 'created_at']) as $p) {
    echo "{$p->id} | {$p->name} | active=".($p->is_active ? 1 : 0).' | stock='.$p->stock.' | variants='.($p->has_variants ? 1 : 0)." | {$p->created_at}\n";
}

echo "\n--- stock filter note ---\n";
echo 'total products='.App\Models\Product::count()."\n";
echo 'active='.App\Models\Product::where('is_active', true)->count()."\n";
echo 'stock=0='.App\Models\Product::where('stock', '<=', 0)->count()."\n";

@unlink(__FILE__);
