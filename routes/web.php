<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WarrantyController;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', function () {
    $baseUrl = rtrim(url('/'), '/');

    $urls = [];
    $urls[] = [
        'loc' => $baseUrl . '/',
        'lastmod' => now()->toDateString(),
        'changefreq' => 'daily',
        'priority' => '1.0',
    ];

    $categories = \App\Models\Category::query()->select(['slug', 'updated_at'])->get();
    foreach ($categories as $category) {
        $urls[] = [
            'loc' => $baseUrl . '/category/' . $category->slug,
            'lastmod' => optional($category->updated_at)->toDateString(),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];
    }

    $products = \App\Models\Product::query()->active()->select(['slug', 'updated_at'])->get();
    foreach ($products as $product) {
        $urls[] = [
            'loc' => $baseUrl . '/product/' . $product->slug,
            'lastmod' => optional($product->updated_at)->toDateString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $item) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>' . e($item['loc']) . '</loc>' . "\n";
        if (!empty($item['lastmod'])) {
            $xml .= '    <lastmod>' . e($item['lastmod']) . '</lastmod>' . "\n";
        }
        if (!empty($item['changefreq'])) {
            $xml .= '    <changefreq>' . e($item['changefreq']) . '</changefreq>' . "\n";
        }
        if (!empty($item['priority'])) {
            $xml .= '    <priority>' . e($item['priority']) . '</priority>' . "\n";
        }
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/test', function() {
    return view('test');
})->name('test');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [HomeController::class, 'showProduct'])->name('product.show');
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/product/buy/{productId}', [HomeController::class, 'buyProduct'])->name('product.buy');
Route::get('/search', [HomeController::class, 'search'])->name('search');

Route::get('/chinh-sach-bao-hanh', function () {
    $categories = \App\Models\Category::with(['children' => function ($q) {
        $q->with('children');
    }])->whereNull('parent_id')->ordered()->get();

    return view('policies.warranty_simple', compact('categories'));
})->name('policies.warranty');

Route::get('/chinh-sach-doi-tra', function () {
    $categories = \App\Models\Category::with(['children' => function ($q) {
        $q->with('children');
    }])->whereNull('parent_id')->ordered()->get();

    return view('policies.returns', compact('categories'));
})->name('policies.returns');

Route::get('/chinh-sach-bao-mat', function () {
    $categories = \App\Models\Category::with(['children' => function ($q) {
        $q->with('children');
    }])->whereNull('parent_id')->ordered()->get();

    return view('policies.privacy', compact('categories'));
})->name('policies.privacy');

Route::get('/phuong-thuc-thanh-toan', function () {
    $categories = \App\Models\Category::with(['children' => function ($q) {
        $q->with('children');
    }])->whereNull('parent_id')->ordered()->get();

    return view('policies.payment', compact('categories'));
})->name('policies.payment');

Route::get('/chinh-sach-giao-hang', function () {
    $categories = \App\Models\Category::with(['children' => function ($q) {
        $q->with('children');
    }])->whereNull('parent_id')->ordered()->get();

    return view('policies.shipping', compact('categories'));
})->name('policies.shipping');

Route::get('/dieu-khoan-su-dung', function () {
    $categories = \App\Models\Category::with(['children' => function ($q) {
        $q->with('children');
    }])->whereNull('parent_id')->ordered()->get();

    return view('policies.terms', compact('categories'));
})->name('policies.terms');

// Newsletter
Route::post('/newsletter/subscribe', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'phone' => ['nullable', 'string', 'max:30'],
        'accept' => ['accepted'],
    ]);

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => 'Đăng ký nhận tin thành công!',
            'data' => [
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ],
        ]);
    }

    return back()->with('newsletter_success', 'Đăng ký nhận tin thành công!');
})->name('newsletter.subscribe');
// Warranty routes
Route::get('/warranty/check', [WarrantyController::class, 'check'])->name('warranty.check');
Route::post('/warranty/search', [WarrantyController::class, 'search'])->name('warranty.search');
Route::post('/warranty/claim', [WarrantyController::class, 'claim'])->name('warranty.claim');

// Tra cứu đơn hàng (public)
Route::get('/tra-cuu-don-hang', [\App\Http\Controllers\OrderController::class, 'lookup'])->name('orders.lookup');
Route::post('/tra-cuu-don-hang', [\App\Http\Controllers\OrderController::class, 'lookupSearch'])->name('orders.lookup.search');

// Lịch sử đơn hàng (public - dùng session cho khách)
Route::get('/lich-su-don-hang', [\App\Http\Controllers\OrderController::class, 'history'])->name('orders.history');

// Báo giá (public - dùng session cho khách)
Route::get('/bao-gia/{orderCode}', [\App\Http\Controllers\OrderController::class, 'quote'])->name('orders.quote');
Route::post('/bao-gia/{orderCode}/xac-nhan', [\App\Http\Controllers\OrderController::class, 'confirmFromQuote'])->name('orders.quote.confirm');
Route::get('/bao-gia/{orderCode}/thanh-cong', [\App\Http\Controllers\OrderController::class, 'quoteSuccess'])->name('orders.quote.success');

// Route group cho admin - đổi prefix sang cp-admin để tránh trùng thư mục public/admin
Route::prefix('cp-admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Trang /admin (root) -> chuyển vào trang quản trị mặc định
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    })->name('dashboard');

    // Product management
    Route::get('products/export-excel', [App\Http\Controllers\Admin\ProductController::class, 'exportExcel'])->name('products.exportExcel');
    Route::post('products/import-excel', [App\Http\Controllers\Admin\ProductController::class, 'importExcel'])->name('products.importExcel');
    Route::post('products/{product}/delete-image', [App\Http\Controllers\Admin\ProductController::class, 'deleteAdditionalImage'])->name('products.deleteImage');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    
    // Category management
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);

    // Warranty management
    Route::get('warranties/claims', [App\Http\Controllers\Admin\WarrantyController::class, 'claims'])->name('warranties.claims');
    Route::patch('warranties/claims/{claim}/status', [App\Http\Controllers\Admin\WarrantyController::class, 'updateClaimStatus'])->name('warranties.claims.update-status');
    // Đặt export/import TRƯỚC resource để tránh bị nuốt bởi {warranty}
    Route::get('warranties/export-excel', [App\Http\Controllers\Admin\WarrantyController::class, 'exportExcel'])->name('warranties.exportExcel');
    Route::post('warranties/import-excel', [App\Http\Controllers\Admin\WarrantyController::class, 'importExcel'])->name('warranties.importExcel');
    Route::post('warranties/destroy-all', [App\Http\Controllers\Admin\WarrantyController::class, 'destroyAll'])->name('warranties.destroyAll');
    Route::resource('warranties', App\Http\Controllers\Admin\WarrantyController::class);

    // Repair Forms Routes
    Route::resource('repair-forms', App\Http\Controllers\Admin\RepairFormController::class);
    Route::get('repair-forms/{repairForm}/export-word', [App\Http\Controllers\Admin\RepairFormController::class, 'exportWord'])->name('repair-forms.exportWord');
    Route::get('repair-forms/{repairForm}/print-modern', [App\Http\Controllers\Admin\RepairFormController::class, 'printModern'])->name('repair-forms.printModern');
    Route::get('repair-forms/{repairForm}/print-return', [App\Http\Controllers\Admin\RepairFormController::class, 'printReturn'])->name('repair-forms.printReturn');
    Route::get('warranties/{warranty}/create-repair-form', [App\Http\Controllers\Admin\RepairFormController::class, 'createFromWarranty'])->name('repair-forms.createFromWarranty');
    Route::get('warranty-claims/{warrantyClaim}/create-repair-form', [App\Http\Controllers\Admin\RepairFormController::class, 'createFromClaim'])->name('repair-forms.createFromClaim');

    // Order management
    Route::get('orders', [\App\Http\Controllers\Admin\OrderAdminController::class, 'index'])->name('orders.index');
    Route::get('orders/{orderId}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'destroy'])->name('orders.destroy');

    // Banners management
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);

    // Users management
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.updateRole');

    // Profile
    Route::get('profile/avatar', [\App\Http\Controllers\Admin\ProfileController::class, 'editAvatar'])->name('profile.avatar.edit');
    Route::post('profile/avatar', [\App\Http\Controllers\Admin\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::get('profile/avatar/image', [\App\Http\Controllers\Admin\ProfileController::class, 'avatarImage'])->name('profile.avatar.image');

    // Activity logs
    Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('notifications', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'markAllRead'])->name('notifications.read_all');
    Route::get('notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'markRead'])->name('notifications.read');
});

Route::get('/thong-bao', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');

Route::middleware('auth')->group(function () {
    Route::post('/thong-bao/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::get('/thong-bao/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');

    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
});

// Cart: allow guest (session-based cart)
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'viewCart'])->name('cart.view');
Route::post('/cart/add/{productId}', [\App\Http\Controllers\CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update/{itemId}', [\App\Http\Controllers\CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove/{itemId}', [\App\Http\Controllers\CartController::class, 'removeFromCart'])->name('cart.remove');

// Checkout: allow guest
Route::get('/checkout', [\App\Http\Controllers\CartController::class, 'showCheckout'])->name('checkout.show');
Route::post('/checkout/info', [\App\Http\Controllers\CartController::class, 'postCheckoutInfo'])->name('checkout.info');
Route::get('/checkout/payment', [\App\Http\Controllers\CartController::class, 'showCheckoutPayment'])->name('checkout.payment');
Route::post('/checkout/confirm', [\App\Http\Controllers\CartController::class, 'confirmOrder'])->name('checkout.confirm');

Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    
    // Reviews
    Route::post('/reviews/{productId}', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{reviewId}/reply', [\App\Http\Controllers\ReviewController::class, 'reply'])->name('reviews.reply');
});

Route::get('/dashboard', function () {
    return view('welcome'); // hoặc trả về view dashboard riêng nếu có
})->name('dashboard');

require __DIR__.'/auth.php';
