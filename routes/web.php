<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatMessage;
use App\Models\ChatQuestionEvent;
use App\Http\Controllers\Admin\ChatAnalyticsController;


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

Route::get('/api/tax-code/{taxCode}', function (string $taxCode) {
    $taxCode = preg_replace('/\s+/', '', trim((string) $taxCode));
    if ($taxCode === '' || strlen($taxCode) < 8) {
        return response()->json([
            'ok' => false,
            'message' => 'Mã số thuế không hợp lệ.',
        ], 422);
    }

    try {
        $res = Http::timeout(10)
            ->acceptJson()
            ->get('https://api.vietqr.io/v2/business/' . urlencode($taxCode));

        if (!$res->ok()) {
            return response()->json([
                'ok' => false,
                'message' => 'Không tra cứu được mã số thuế.',
            ], 502);
        }

        $json = $res->json();
        $data = is_array($json) ? ($json['data'] ?? null) : null;

        $name = is_array($data) ? (string) ($data['name'] ?? '') : '';
        $address = is_array($data) ? (string) ($data['address'] ?? '') : '';

        return response()->json([
            'ok' => true,
            'data' => [
                'tax_code' => $taxCode,
                'name' => $name,
                'address' => $address,
            ],
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Có lỗi khi tra cứu mã số thuế.',
        ], 500);
    }
})->name('api.tax-code.lookup');

Route::get('/api/chat/product-lookup', function (Request $request) {
    $q = trim((string) $request->query('q', ''));
    if ($q === '' || mb_strlen($q) < 2) {
        return response()->json([
            'ok' => false,
            'message' => 'Từ khoá tìm kiếm không hợp lệ.',
            'items' => [],
        ], 422);
    }

    // Tách từ khoá thành nhiều token để tăng khả năng match
    $normalized = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $q) ?? $q;
    $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
    $tokens = array_filter(explode(' ', $normalized), function ($t) {
        return mb_strlen($t) >= 2;
    });

    $products = \App\Models\Product::query()
        ->select(['id', 'name', 'slug', 'price', 'discount_percent', 'serial_number'])
        ->where(function ($query) use ($q, $tokens) {
            // Match nguyên chuỗi trước
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('serial_number', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });

            // Sau đó match theo từng token (AND giữa các token)
            foreach ($tokens as $token) {
                $query->orWhere(function ($q3) use ($token) {
                    $q3->where('name', 'like', "%{$token}%")
                        ->orWhere('serial_number', 'like', "%{$token}%")
                        ->orWhere('slug', 'like', "%{$token}%");
                });
            }
        })
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->orderByDesc('created_at')
        ->limit(3)
        ->get();

    $items = $products->map(function ($p) {
        $image = (string) ($p->image ?? '');
        $imageUrl = $image !== '' ? asset('images/products/' . ltrim($image, '/')) : '';
        return [
            'name' => (string) $p->name,
            'serial_number' => (string) ($p->serial_number ?? ''),
            'price' => (int) ($p->price ?? 0),
            'final_price' => (int) ($p->final_price ?? ($p->price ?? 0)),
            'has_discount' => (bool) ($p->has_discount ?? false),
            'discount_percent' => (int) ($p->discount_percent ?? 0),
            'url' => url('/product/' . $p->slug),
            'image_url' => $imageUrl,
        ];
    })->values();

    return response()->json([
        'ok' => true,
        'query' => $q,
        'items' => $items,
    ]);
})->name('api.chat.product-lookup');

// Chi tiết sản phẩm (dùng cho chế độ chat offline, không cần OpenAI)
Route::get('/api/chat/product-details', function (Request $request) {
    $q = trim((string) $request->query('q', ''));
    $limit = (int) $request->query('limit', 1);
    if ($limit < 1) $limit = 1;
    if ($limit > 3) $limit = 3;

    if ($q === '' || mb_strlen($q) < 2) {
        return response()->json([
            'ok' => false,
            'message' => 'Từ khoá tìm kiếm không hợp lệ.',
            'items' => [],
        ], 422);
    }

    $products = \App\Models\Product::query()
        ->select([
            'id',
            'name',
            'slug',
            'serial_number',
            'price',
            'discount_percent',
            'image',
            'description',
            'information',
            'specifications',
            'instruction',
        ])
        ->where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('serial_number', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%")
                ->orWhere('brand', 'like', "%{$q}%");
        })
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->orderByDesc('created_at')
        ->limit($limit)
        ->get();

    $items = $products->map(function ($p) {
        $image = (string) ($p->image ?? '');
        $imageUrl = $image !== '' ? asset('images/products/' . ltrim($image, '/')) : '';

        $spec = (string) ($p->specifications ?? '');
        $instr = (string) ($p->instruction ?? '');
        $info = (string) ($p->information ?? '');
        $desc = (string) ($p->description ?? '');

        $truncate = function (string $text, int $max) {
            $t = trim($text);
            if ($t === '') return '';
            // Giới hạn độ dài để tránh quá tải payload
            if (mb_strlen($t) > $max) {
                $t = mb_substr($t, 0, $max) . '...';
            }
            return $t;
        };

        return [
            'name' => (string) $p->name,
            'serial_number' => (string) ($p->serial_number ?? ''),
            'url' => url('/product/' . $p->slug),
            'image_url' => $imageUrl,
            'price' => (int) ($p->price ?? 0),
            'final_price' => (int) ($p->final_price ?? ($p->price ?? 0)),
            'has_discount' => (bool) ($p->has_discount ?? false),
            'discount_percent' => (int) ($p->discount_percent ?? 0),
            'description' => $truncate($desc, 900),
            'information' => $truncate($info, 900),
            'specifications' => $truncate($spec, 1400),
            'instruction' => $truncate($instr, 1200),
        ];
    })->values();

    return response()->json([
        'ok' => true,
        'query' => $q,
        'items' => $items,
    ]);
})->name('api.chat.product-details');

// Tìm sản phẩm theo keyword trong mô tả/thông số/hướng dẫn
Route::get('/api/chat/spec-lookup', function (Request $request) {
    $q = trim((string) $request->query('q', ''));
    $limit = (int) $request->query('limit', 3);
    if ($limit < 1) $limit = 1;
    if ($limit > 5) $limit = 5;

    if ($q === '' || mb_strlen($q) < 2) {
        return response()->json([
            'ok' => false,
            'message' => 'Từ khoá tìm kiếm không hợp lệ.',
            'items' => [],
        ], 422);
    }

    $products = \App\Models\Product::query()
        ->select([
            'id',
            'name',
            'slug',
            'serial_number',
            'price',
            'discount_percent',
            'image',
            'specifications',
            'description',
        ])
        ->where(function ($query) use ($q) {
            $like = "%{$q}%";
            $query->where('specifications', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('instruction', 'like', $like);
        })
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->orderByDesc('created_at')
        ->limit($limit)
        ->get();

    $items = $products->map(function ($p) {
        $image = (string) ($p->image ?? '');
        $imageUrl = $image !== '' ? asset('images/products/' . ltrim($image, '/')) : '';
        $spec = (string) ($p->specifications ?? '');

        $truncate = function (string $text, int $max) {
            $t = trim($text);
            if ($t === '') return '';
            if (mb_strlen($t) > $max) {
                $t = mb_substr($t, 0, $max) . '...';
            }
            return $t;
        };

        return [
            'name' => (string) $p->name,
            'serial_number' => (string) ($p->serial_number ?? ''),
            'url' => url('/product/' . $p->slug),
            'image_url' => $imageUrl,
            'price' => (int) ($p->price ?? 0),
            'final_price' => (int) ($p->final_price ?? ($p->price ?? 0)),
            'has_discount' => (bool) ($p->has_discount ?? false),
            'discount_percent' => (int) ($p->discount_percent ?? 0),
            'specifications' => $truncate($spec, 800),
        ];
    })->values();

    return response()->json([
        'ok' => true,
        'query' => $q,
        'items' => $items,
    ]);
})->name('api.chat.spec-lookup');

Route::get('/api/chat/fingerprint-lookup', function (Request $request) {
    $min = (int) $request->query('min', 0);
    if ($min < 1) {
        return response()->json([
            'ok' => false,
            'message' => 'Giá trị min không hợp lệ.',
            'items' => [],
        ], 422);
    }

    $products = \App\Models\Product::query()
        ->active()
        ->select(['id', 'name', 'slug', 'price', 'discount_percent', 'serial_number', 'image', 'specifications'])
        ->where(function ($q) {
            $q->where('specifications', 'like', '%vân tay%')
                ->orWhere('specifications', 'like', '%van tay%')
                ->orWhere('description', 'like', '%vân tay%')
                ->orWhere('description', 'like', '%van tay%');
        })
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->orderByDesc('created_at')
        ->limit(60)
        ->get();

    $matched = $products->map(function ($p) {
        $text = (string) ($p->specifications ?? $p->description ?? '');
        $value = null;

        if (preg_match('/(vân\s*tay|van\s*tay)[^\d]{0,30}([0-9][0-9\.,\s]{1,12})/iu', $text, $m)) {
            $raw = preg_replace('/[^0-9]/', '', (string) ($m[2] ?? ''));
            if ($raw !== '') $value = (int) $raw;
        }

        if ($value === null && preg_match('/([0-9][0-9\.,\s]{1,12})\s*(vân\s*tay|van\s*tay)/iu', $text, $m2)) {
            $raw2 = preg_replace('/[^0-9]/', '', (string) ($m2[1] ?? ''));
            if ($raw2 !== '') $value = (int) $raw2;
        }

        return [
            'product' => $p,
            'fingerprint_capacity' => $value,
        ];
    })->filter(function ($row) use ($min) {
        return ($row['fingerprint_capacity'] ?? 0) >= $min;
    })->sortByDesc('fingerprint_capacity')->values();

    $items = $matched->take(3)->map(function ($row) {
        /** @var \App\Models\Product $p */
        $p = $row['product'];
        $image = (string) ($p->image ?? '');
        $imageUrl = $image !== '' ? asset('images/products/' . ltrim($image, '/')) : '';
        return [
            'name' => (string) $p->name,
            'serial_number' => (string) ($p->serial_number ?? ''),
            'price' => (int) ($p->price ?? 0),
            'final_price' => (int) ($p->final_price ?? ($p->price ?? 0)),
            'has_discount' => (bool) ($p->has_discount ?? false),
            'discount_percent' => (int) ($p->discount_percent ?? 0),
            'url' => url('/product/' . $p->slug),
            'image_url' => $imageUrl,
            'fingerprint_capacity' => (int) ($row['fingerprint_capacity'] ?? 0),
        ];
    })->values();

    return response()->json([
        'ok' => true,
        'min' => $min,
        'items' => $items,
    ]);
})->name('api.chat.fingerprint-lookup');

Route::get('/api/chat/user-capacity-lookup', function (Request $request) {
    $min = (int) $request->query('min', 0);
    if ($min < 1) {
        return response()->json([
            'ok' => false,
            'message' => 'Tham số min không hợp lệ.',
            'items' => [],
        ], 422);
    }

    $products = \App\Models\Product::query()
        ->active()
        ->select(['id', 'name', 'slug', 'price', 'discount_percent', 'serial_number', 'image', 'specifications', 'description'])
        ->where(function ($q) {
            $q->where('name', 'like', '%chấm công%')
              ->orWhere('name', 'like', '%cham cong%')
              ->orWhere('specifications', 'like', '%người dùng%')
              ->orWhere('specifications', 'like', '%nguoi dung%')
              ->orWhere('specifications', 'like', '%user%')
              ->orWhere('description', 'like', '%người dùng%')
              ->orWhere('description', 'like', '%nguoi dung%')
              ->orWhere('description', 'like', '%user%');
        })
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->orderByDesc('created_at')
        ->limit(120)
        ->get();

    $extract = function (?string $text): ?int {
        $t = (string) ($text ?? '');
        if ($t === '') return null;
        $t = mb_strtolower($t);

        // ưu tiên pattern có ngữ cảnh "người dùng"/"user"
        $patterns = [
            '/(nguoi\s*dung|người\s*dùng|nhan\s*su|nhân\s*sự|users?)\D{0,30}([0-9][0-9\.,\s]{0,10})/u',
            '/([0-9][0-9\.,\s]{0,10})\s*(nguoi\s*dung|người\s*dùng|nhan\s*su|nhân\s*sự|users?)/u',
        ];
        foreach ($patterns as $re) {
            if (preg_match($re, $t, $m)) {
                $raw = preg_replace('/[^0-9]/', '', (string) ($m[2] ?? $m[1] ?? ''));
                if ($raw !== '') {
                    $n = (int) $raw;
                    if ($n > 0) return $n;
                }
            }
        }
        return null;
    };

    $items = $products->map(function ($p) use ($extract) {
        $spec = $extract((string) ($p->specifications ?? ''));
        $desc = $extract((string) ($p->description ?? ''));
        $cap = $spec ?? $desc;

        $image = (string) ($p->image ?? '');
        $imageUrl = $image !== '' ? asset('images/products/' . ltrim($image, '/')) : '';

        return [
            'name' => (string) $p->name,
            'serial_number' => (string) ($p->serial_number ?? ''),
            'price' => (int) ($p->price ?? 0),
            'final_price' => (int) ($p->final_price ?? ($p->price ?? 0)),
            'has_discount' => (bool) ($p->has_discount ?? false),
            'discount_percent' => (int) ($p->discount_percent ?? 0),
            'url' => url('/product/' . $p->slug),
            'image_url' => $imageUrl,
            'user_capacity' => $cap,
        ];
    })->filter(function ($it) use ($min) {
        $cap = (int) ($it['user_capacity'] ?? 0);
        return $cap >= $min;
    })->sortByDesc(function ($it) {
        return (int) ($it['user_capacity'] ?? 0);
    })->values()->take(3)->values();

    return response()->json([
        'ok' => true,
        'min' => $min,
        'items' => $items,
    ]);
})->name('api.chat.user-capacity-lookup');

Route::post('/api/chat/track-question', function (Request $request) {
    $data = $request->validate([
        'guest_id' => ['nullable', 'string', 'max:64'],
        'intent' => ['nullable', 'string', 'max:40'],
        'is_unanswered' => ['nullable', 'boolean'],
        'unanswered_reason' => ['nullable', 'string', 'max:80'],
        'text' => ['required', 'string', 'max:2000'],
        'normalized_text' => ['nullable', 'string', 'max:500'],
        'page_url' => ['nullable', 'string', 'max:500'],
    ]);

    $user = $request->user();
    $text = (string) ($data['text'] ?? '');
    $normalized = (string) ($data['normalized_text'] ?? '');
    if ($normalized === '') {
        $normalized = mb_strtolower(trim($text));
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
    }

    ChatQuestionEvent::create([
        'user_id' => $user ? $user->id : null,
        'guest_id' => (string) ($data['guest_id'] ?? ''),
        'intent' => (string) ($data['intent'] ?? 'unknown'),
        'is_unanswered' => (bool) ($data['is_unanswered'] ?? false),
        'unanswered_reason' => (string) ($data['unanswered_reason'] ?? ''),
        'text' => $text,
        'normalized_text' => $normalized,
        'page_url' => (string) ($data['page_url'] ?? ''),
        'ip' => (string) ($request->ip() ?? ''),
        'user_agent' => (string) ($request->userAgent() ?? ''),
    ]);

    return response()->json(['ok' => true]);
})->name('api.chat.track-question');

Route::get('/api/chat/messages', function (Request $request) {
    $limit = (int) $request->query('limit', 50);
    if ($limit < 1) $limit = 1;
    if ($limit > 100) $limit = 100;

    $user = $request->user();
    $guestId = trim((string) $request->query('guest_id', ''));

    if (!$user && $guestId === '') {
        return response()->json([
            'ok' => false,
            'message' => 'Thiếu guest_id.',
            'items' => [],
        ], 422);
    }

    $q = ChatMessage::query();
    if ($user) {
        $q->where('user_id', $user->id);
    } else {
        $q->whereNull('user_id')->where('guest_id', $guestId);
    }

    $msgs = $q->orderByDesc('id')
        ->limit($limit)
        ->get(['id', 'type', 'text', 'created_at']);

    return response()->json([
        'ok' => true,
        'items' => $msgs->reverse()->values(),
    ]);
})->name('api.chat.messages.index');

Route::post('/api/chat/messages', function (Request $request) {
    $data = $request->validate([
        'type' => ['required', 'string', 'in:user,staff,system'],
        'text' => ['required', 'string', 'max:5000'],
        'guest_id' => ['nullable', 'string', 'max:64'],
    ]);

    $user = $request->user();
    $guestId = trim((string) ($data['guest_id'] ?? ''));

    if (!$user && $guestId === '') {
        return response()->json([
            'ok' => false,
            'message' => 'Thiếu guest_id.',
        ], 422);
    }

    if (!$user) {
        if (!Str::startsWith($guestId, 'g_') || strlen($guestId) < 8) {
            return response()->json([
                'ok' => false,
                'message' => 'guest_id không hợp lệ.',
            ], 422);
        }
    }

    $msg = ChatMessage::create([
        'user_id' => $user ? $user->id : null,
        'guest_id' => $user ? null : $guestId,
        'type' => $data['type'],
        'text' => $data['text'],
    ]);

    return response()->json([
        'ok' => true,
        'item' => $msg->only(['id', 'type', 'text', 'created_at']),
    ], 201);
})->name('api.chat.messages.store');

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
    Route::get('products/lookup', [App\Http\Controllers\Admin\ProductController::class, 'lookup'])->name('products.lookup');
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

    Route::get('customers/lookup', [\App\Http\Controllers\Admin\CustomerController::class, 'lookup'])->name('customers.lookup');
    Route::get('customers/tax-lookup/{taxCode}', [\App\Http\Controllers\Admin\CustomerController::class, 'taxLookup'])->name('customers.taxLookup');
    Route::get('customers/import', [\App\Http\Controllers\Admin\CustomerController::class, 'importForm'])->name('customers.import.form');
    Route::post('customers/import-excel', [\App\Http\Controllers\Admin\CustomerController::class, 'importExcel'])->name('customers.importExcel');
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);

    Route::resource('borrow-requests', \App\Http\Controllers\Admin\BorrowRequestController::class);

    Route::get('chat-support', [\App\Http\Controllers\Admin\ChatSupportController::class, 'index'])->name('chat-support.index');
    Route::get('chat-support/unread', [\App\Http\Controllers\Admin\ChatSupportController::class, 'unread'])->name('chat-support.unread');
    Route::get('chat-support/thread', [\App\Http\Controllers\Admin\ChatSupportController::class, 'thread'])->name('chat-support.thread');
    Route::post('chat-support/send', [\App\Http\Controllers\Admin\ChatSupportController::class, 'send'])->name('chat-support.send');

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
