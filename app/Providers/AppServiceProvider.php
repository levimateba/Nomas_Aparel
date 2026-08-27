<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Setting;
use App\Models\WishlistItem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');

        View::composer('*', function ($view) {
            $shopCategories = collect();
            if (Schema::hasTable('categories')) {
                $shopCategories = Category::query()
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->get();
            }

            $view->with([
                'settings' => Setting::get_settings(),
                'siteContacts' => Schema::hasTable('contacts') ? Contact::orderBy('id')->get() : collect(),
                'shopCategories' => $shopCategories,
                'wishlistIds' => (auth()->check() && Schema::hasTable('wishlist_items'))
                    ? WishlistItem::query()->where('user_id', auth()->id())->pluck('product_id')->all()
                    : [],
                'cartCount' => collect(session('cart', []))->sum('qty'),
                'cartTotal' => collect(session('cart', []))->sum(fn ($item) => ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0))),
            ]);
        });
    }
}
