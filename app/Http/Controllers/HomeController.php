<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh mục cha và con nhiều cấp (menu đa cấp)
        $categories = \App\Models\Category::with(['children' => function($q) {
            $q->with('children');
        }])->whereNull('parent_id')->ordered()->get();

        // Query sản phẩm với filter khoảng giá - CHỈ hiển thị sản phẩm có status = 1 (hiển thị)
        $query = Product::with('category')->active();
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        $products = $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->paginate(20)->appends($request->only(['price_min', 'price_max']));

        // Lấy danh mục cha + danh mục con (cho layout CellphoneS style)
        $categoryWithProducts = \App\Models\Category::with(['children'])->whereNull('parent_id')->ordered()->get();
        
        // Tối ưu: Lấy tất cả category IDs (cha + con) trong 1 lần
        $allCategoryIds = [];
        $categoryMapping = []; // Map category_id => parent_category_id
        foreach ($categoryWithProducts as $cat) {
            $allCategoryIds[] = $cat->id;
            $categoryMapping[$cat->id] = $cat->id;
            foreach ($cat->children as $child) {
                $allCategoryIds[] = $child->id;
                $categoryMapping[$child->id] = $cat->id; // Map con về cha
            }
        }
        
        // Lấy tất cả sản phẩm trong 1 query duy nhất
        $allProducts = Product::whereIn('category_id', $allCategoryIds)
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();
        
        // Group sản phẩm theo danh mục cha (KHÔNG limit để lấy đủ brands)
        foreach ($categoryWithProducts as $cat) {
            $cat->allProducts = $allProducts->filter(function($product) use ($cat, $categoryMapping) {
                return ($categoryMapping[$product->category_id] ?? null) === $cat->id;
            })->values();
        }

        // Lấy 6 sản phẩm nổi bật cho header - sử dụng scope featured()
        $featuredProducts = Product::with('category')
            ->featured()
            ->orderByDesc('created_at')
            ->take(6)
            ->get();
        if ($featuredProducts->count() < 6) {
            $more = Product::with('category')
                ->active()
                ->orderByDesc('created_at')
                ->whereNotIn('id', $featuredProducts->pluck('id'))
                ->take(6 - $featuredProducts->count())
                ->get();
            $featuredProducts = $featuredProducts->concat($more);
        }

        // Lấy sản phẩm hot sale cuối tuần - sử dụng scope featured()
        $hotSaleProducts = Product::with('category')
            ->featured()
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Load wishlist items một lần để tránh N+1 query trong view
        $wishlistProductIds = collect();
        if (auth()->check()) {
            $wishlistProductIds = \App\Models\Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Nhóm banner nhỏ dưới hero (giống CellphoneS / Phong Vũ)
        $homePromoBanners = Banner::active()
            ->position('home_promo')
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        return view('home.index', compact(
            'categories',
            'products',
            'featuredProducts',
            'hotSaleProducts',
            'wishlistProductIds',
            'categoryWithProducts',
            'homePromoBanners'
        ));
    }

    public function showProduct($slug)
    {
        // CHỈ hiển thị sản phẩm có status = 1 (hiển thị)
        $product = Product::with(['category', 'images'])->active()->where('slug', $slug)->firstOrFail();
        $categories = Category::with('children')->whereNull('parent_id')->ordered()->get();
        // Sản phẩm khác: random từ tất cả sản phẩm (không giới hạn danh mục)
        $relatedProducts = Product::with('category')
            ->active()
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(8)
            ->get();
        // Gộp 2 queries thành 1 để tối ưu performance
        $addonsQuery = $product->addonsWithProduct();
        $addons = $addonsQuery->take(6)->get();
        $totalAddons = $addonsQuery->count();
        
        // Load reviews với user và replies
        $reviews = $product->reviews()->with(['user', 'replies.user'])->get();
        $reviewStats = [
            'total' => $reviews->count(),
            'average' => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0,
            'performance_avg' => $reviews->count() > 0 ? round($reviews->avg('performance_rating'), 1) : 0,
            'durability_avg' => $reviews->count() > 0 ? round($reviews->avg('durability_rating'), 1) : 0,
            'stars' => [
                5 => $reviews->where('rating', 5)->count(),
                4 => $reviews->where('rating', 4)->count(),
                3 => $reviews->where('rating', 3)->count(),
                2 => $reviews->where('rating', 2)->count(),
                1 => $reviews->where('rating', 1)->count(),
            ],
        ];
        
        return view('product.show', compact('product', 'categories', 'relatedProducts', 'addons', 'totalAddons', 'reviews', 'reviewStats'));
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q'));
        $categories = \App\Models\Category::with(['children' => function($q) {
            $q->with('children');
        }])->whereNull('parent_id')->ordered()->get();
        // Tìm kiếm cũng CHỈ hiển thị sản phẩm có status = 1 (hiển thị)
        $products = Product::with('category')
            ->active()
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('serial_number', 'like', "%$q%")
                      ->orWhere('slug', 'like', "%$q%")
                ;
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(['q' => $q]);
        return view('search.results', compact('products', 'categories', 'q'));
    }

    // Giả sử có hàm addToCart và buyProduct, thêm kiểm tra auth
    public function addToCart(Request $request, $productId)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('status', 'Bạn cần đăng nhập để thêm vào giỏ hàng!');
        }
        // ... logic thêm vào giỏ ...
    }

    public function buyProduct(Request $request, $productId)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('status', 'Bạn cần đăng nhập để mua hàng!');
        }
        // ... logic mua hàng ...
    }
}
