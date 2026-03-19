<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Support\ActivityLogger;

class CategoryController extends Controller
{
    public function index()
    {
        // Lấy toàn bộ danh mục để hiển thị tree view nhiều cấp
        $categories = \App\Models\Category::with('parent')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        // Lấy tất cả danh mục để chọn cha (dạng cây)
        $parents = Category::orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'promo_banner' => 'nullable|image|max:4096',
        ]);
        $data['slug'] = Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category = Category::create($data);

        ActivityLogger::log('category.create', $category, 'Thêm danh mục: ' . ($category->name ?? ''), [
            'name' => $category->name ?? null,
            'parent_id' => $category->parent_id ?? null,
        ], $request);

        if ($request->hasFile('promo_banner')) {
            $file = $request->file('promo_banner');
            $filename = 'cat_promo_' . $category->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/banners'), $filename);
            $category->update(['promo_banner' => $filename]);
        }
        return redirect()->route('admin.categories.index')->with('success', 'Thêm danh mục thành công!');
    }

    public function edit(Category $category)
    {
        // Không cho chọn chính nó hoặc con của nó làm cha
        $parents = Category::where('id', '!=', $category->id)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $before = $category->only(['name', 'parent_id', 'sort_order', 'banner_image_1', 'banner_image_2', 'promo_banner']);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'banner_image_1' => 'nullable|image|max:2048',
            'banner_image_2' => 'nullable|image|max:2048',
            'promo_banner' => 'nullable|image|max:4096',
            'remove_banner_image_1' => 'nullable|boolean',
            'remove_banner_image_2' => 'nullable|boolean',
            'remove_promo_banner' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Upload banner 1
        if ($request->hasFile('banner_image_1')) {
            if (!empty($category->banner_image_1)) {
                $oldPath = public_path('images/categories/' . $category->banner_image_1);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('banner_image_1');
            $filename = 'cat_banner_' . $category->id . '_1_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/categories'), $filename);
            $data['banner_image_1'] = $filename;
        }

        if ($request->boolean('remove_banner_image_1')) {
            if (!empty($category->banner_image_1)) {
                $oldPath = public_path('images/categories/' . $category->banner_image_1);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $data['banner_image_1'] = null;
        }

        // Upload banner 2
        if ($request->hasFile('banner_image_2')) {
            if (!empty($category->banner_image_2)) {
                $oldPath = public_path('images/categories/' . $category->banner_image_2);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('banner_image_2');
            $filename = 'cat_banner_' . $category->id . '_2_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/categories'), $filename);
            $data['banner_image_2'] = $filename;
        }

        if ($request->boolean('remove_banner_image_2')) {
            if (!empty($category->banner_image_2)) {
                $oldPath = public_path('images/categories/' . $category->banner_image_2);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $data['banner_image_2'] = null;
        }

        // Upload promo banner (product detail)
        if ($request->hasFile('promo_banner')) {
            if (!empty($category->promo_banner)) {
                $oldPath = public_path('images/banners/' . $category->promo_banner);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('promo_banner');
            $filename = 'cat_promo_' . $category->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/banners'), $filename);
            $data['promo_banner'] = $filename;
        }

        if ($request->boolean('remove_promo_banner')) {
            if (!empty($category->promo_banner)) {
                $oldPath = public_path('images/banners/' . $category->promo_banner);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $data['promo_banner'] = null;
        }

        $category->update($data);

        $after = $category->fresh()->only(['name', 'parent_id', 'sort_order', 'banner_image_1', 'banner_image_2', 'promo_banner']);
        ActivityLogger::log('category.update', $category, 'Cập nhật danh mục: ' . ($category->name ?? ''), [
            'before' => $before,
            'after' => $after,
        ], $request);
        return redirect()->route('admin.categories.index')->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(Category $category)
    {
        ActivityLogger::log('category.delete', $category, 'Xóa danh mục: ' . ($category->name ?? ''), [
            'name' => $category->name ?? null,
        ]);
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa danh mục!');
    }
}