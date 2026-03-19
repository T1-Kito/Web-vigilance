<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Banner;

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
        // Đăng ký Observer cho Product
        if (class_exists('App\\Observers\\ProductObserver')) {
            Product::observe('App\\Observers\\ProductObserver');
        }
        
        View::composer('*', function ($view) {
            $cartCount = 0;
            if (Auth::check()) {
                $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
            } else {
                $guestCart = session()->get('guest_cart', []);
                foreach ($guestCart as $row) {
                    $cartCount += (int) ($row['quantity'] ?? 0);
                }
            }
            $view->with('cartCount', $cartCount);
        });

        View::composer('layouts.user', function ($view) {
            $sideBanners = Cache::remember('banners.side', now()->addMinutes(5), function () {
                $left = Banner::query()
                    ->active()
                    ->position('side_left')
                    ->orderBy('sort_order')
                    ->select(['id', 'image_path', 'link_url', 'position', 'is_active', 'sort_order'])
                    ->first();

                $right = Banner::query()
                    ->active()
                    ->position('side_right')
                    ->orderBy('sort_order')
                    ->select(['id', 'image_path', 'link_url', 'position', 'is_active', 'sort_order'])
                    ->first();

                return [
                    'left' => $left,
                    'right' => $right,
                ];
            });

            $view->with('sideLeftBanner', $sideBanners['left'] ?? null);
            $view->with('sideRightBanner', $sideBanners['right'] ?? null);
        });
    }
}
