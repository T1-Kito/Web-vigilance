<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $categories = Category::with(['children' => function($q) {
            $q->with('children');
        }])->whereNull('parent_id')->ordered()->get();

        $categoryIds = $category->descendantIds();

        $baseQuery = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->active();

        $selectedFilter = $request->query('filter', 'all');
        $allowedFilters = ['all', 'new', 'hot'];
        if (!in_array($selectedFilter, $allowedFilters, true)) {
            $selectedFilter = 'all';
        }

        if ($selectedFilter === 'hot') {
            $baseQuery->where('is_featured', 1);
        }

        if ($selectedFilter === 'new') {
            $baseQuery->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $availableBrands = [];
        $selectedBrand = $request->query('brand');

        $hasBrandColumn = Schema::hasColumn('products', 'brand');

        if ($hasBrandColumn) {
            $brandCounts = (clone $baseQuery)
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->selectRaw('brand, COUNT(*) as aggregate')
                ->groupBy('brand')
                ->orderBy('brand')
                ->pluck('aggregate', 'brand');

            foreach ($brandCounts as $brandLabel => $count) {
                $availableBrands[$brandLabel] = [
                    'label' => $brandLabel,
                    'count' => $count,
                ];
            }

            if ($selectedBrand) {
                $baseQuery->where('brand', $selectedBrand);
            } else {
                $selectedBrand = null;
            }
        } else {
            $brandDefinitions = [
                'zkteco' => ['label' => 'ZKTeco', 'patterns' => ['zkteco']],
                'dahua' => ['label' => 'Dahua', 'patterns' => ['dahua']],
                'hikvision' => ['label' => 'Hikvision', 'patterns' => ['hikvision']],
                'kbvision' => ['label' => 'KBVision', 'patterns' => ['kbvision', 'kb vision']],
                'imou' => ['label' => 'Imou', 'patterns' => ['imou']],
                'ezviz' => ['label' => 'Ezviz', 'patterns' => ['ezviz']],
            ];

            $brandCounts = [];
            $productNames = (clone $baseQuery)->pluck('name');
            foreach ($productNames as $name) {
                $lower = mb_strtolower($name);
                foreach ($brandDefinitions as $slugKey => $def) {
                    foreach ($def['patterns'] as $pattern) {
                        if (str_contains($lower, $pattern)) {
                            $brandCounts[$slugKey] = ($brandCounts[$slugKey] ?? 0) + 1;
                            continue 3;
                        }
                    }
                }
            }

            foreach ($brandDefinitions as $slugKey => $def) {
                if (!empty($brandCounts[$slugKey])) {
                    $availableBrands[$slugKey] = [
                        'label' => $def['label'],
                        'count' => $brandCounts[$slugKey],
                    ];
                }
            }

            if ($selectedBrand && isset($brandDefinitions[$selectedBrand])) {
                $patterns = $brandDefinitions[$selectedBrand]['patterns'];
                $baseQuery->where(function ($q) use ($patterns) {
                    foreach ($patterns as $pattern) {
                        $q->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($pattern) . '%']);
                    }
                });
            } else {
                $selectedBrand = null;
            }
        }

        $selectedSort = $request->query('sort', 'newest');
        $allowedSorts = ['newest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'];
        if (!in_array($selectedSort, $allowedSorts, true)) {
            $selectedSort = 'newest';
        }

        if ($selectedSort === 'price_asc') {
            $baseQuery->orderBy('price', 'asc');
        } elseif ($selectedSort === 'price_desc') {
            $baseQuery->orderBy('price', 'desc');
        } elseif ($selectedSort === 'name_asc') {
            $baseQuery->orderBy('name', 'asc');
        } elseif ($selectedSort === 'name_desc') {
            $baseQuery->orderBy('name', 'desc');
        } else {
            $baseQuery->orderBy('created_at', 'desc');
        }

        $products = $baseQuery->with('category')
            ->paginate(12)
            ->appends($request->query());

        return view('category.show', compact('category', 'categories', 'products', 'availableBrands', 'selectedBrand', 'selectedSort', 'selectedFilter'));
    }
}