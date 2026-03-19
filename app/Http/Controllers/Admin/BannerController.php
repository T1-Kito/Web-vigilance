<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Banner;
use App\Support\ActivityLogger;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'position' => 'required|string|in:side_left,side_right,general,home_promo,top_strip',
            'title' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:2048',
            // top_strip cho phép mp4/webm; các vị trí khác chỉ cho ảnh
            'image' => [
                'required',
                'file',
                'max:20480', // 20MB (video cần lớn hơn ảnh)
                function ($attribute, $value, $fail) use ($request) {
                    $pos = (string) $request->input('position', 'general');
                    $ext = strtolower($value?->getClientOriginalExtension() ?? '');
                    $imgExt = ['jpg','jpeg','png','gif','webp'];
                    $vidExt = ['mp4','webm'];
                    if ($pos === 'top_strip') {
                        if (!in_array($ext, array_merge($imgExt, $vidExt), true)) {
                            $fail('Thanh banner trên cùng chỉ nhận ảnh (jpg/png/webp/...) hoặc video (mp4/webm).');
                        }
                    } else {
                        if (!in_array($ext, $imgExt, true)) {
                            $fail('Banner chỉ nhận file ảnh (jpg/jpeg/png/gif/webp).');
                        }
                    }
                },
            ],
        ]);

        $data = [];
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = time().'_banner_'.uniqid().'.'.$ext;
                
            // Sửa đường dẫn cho Vinahost
            $uploadPath = in_array($ext, ['mp4','webm'], true) ? 'videos/banners' : 'images/banners';
            $fullPath = public_path($uploadPath);
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            $file->move($fullPath, $filename);
            $data['image_path'] = $uploadPath . '/' . $filename;
            
            // Debug log
            \Log::info('Banner upload', [
                'filename' => $filename,
                'upload_path' => $uploadPath,
                'full_path' => $fullPath,
                'final_path' => $data['image_path'],
                'file_exists' => file_exists($fullPath . '/' . $filename)
            ]);
        }

        $data['title'] = $request->input('title');
        $data['link_url'] = $request->input('link_url');
        $data['position'] = $request->input('position', 'general');
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['sort_order'] = (int) (Banner::max('sort_order') + 1);

        $banner = Banner::create($data);
        ActivityLogger::log('banner.create', $banner, 'Thêm banner', [
            'position' => $banner->position ?? null,
            'title' => $banner->title ?? null,
        ], $request);
        Cache::forget('banners.side');
        return redirect()->route('admin.banners.index')->with('success', 'Thêm banner thành công!');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $before = $banner->only(['position', 'title', 'link_url', 'image_path', 'sort_order', 'is_active']);
        $request->validate([
            'position' => 'required|string|in:side_left,side_right,general,home_promo,top_strip',
            'title' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'image' => [
                'nullable',
                'file',
                'max:20480',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value) return;
                    $pos = (string) $request->input('position', 'general');
                    $ext = strtolower($value?->getClientOriginalExtension() ?? '');
                    $imgExt = ['jpg','jpeg','png','gif','webp'];
                    $vidExt = ['mp4','webm'];
                    if ($pos === 'top_strip') {
                        if (!in_array($ext, array_merge($imgExt, $vidExt), true)) {
                            $fail('Thanh banner trên cùng chỉ nhận ảnh (jpg/png/webp/...) hoặc video (mp4/webm).');
                        }
                    } else {
                        if (!in_array($ext, $imgExt, true)) {
                            $fail('Banner chỉ nhận file ảnh (jpg/jpeg/png/gif/webp).');
                        }
                    }
                },
            ],
        ]);

        $data = [];
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = time().'_banner_'.uniqid().'.'.$ext;
            
            // Sửa đường dẫn cho Vinahost
            $uploadPath = in_array($ext, ['mp4','webm'], true) ? 'videos/banners' : 'images/banners';
            $fullPath = public_path($uploadPath);
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            $file->move($fullPath, $filename);
            $data['image_path'] = $uploadPath . '/' . $filename;
            
            // Debug log
            \Log::info('Banner update', [
                'filename' => $filename,
                'upload_path' => $uploadPath,
                'full_path' => $fullPath,
                'final_path' => $data['image_path'],
                'file_exists' => file_exists($fullPath . '/' . $filename)
            ]);
        }

        $data['title'] = $request->input('title');
        $data['link_url'] = $request->input('link_url');
        $data['position'] = $request->input('position', $banner->position ?? 'general');
        if ($request->filled('sort_order')) {
            $data['sort_order'] = (int) $request->input('sort_order');
        }
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $banner->update($data);
        $after = $banner->fresh()->only(['position', 'title', 'link_url', 'image_path', 'sort_order', 'is_active']);
        ActivityLogger::log('banner.update', $banner, 'Cập nhật banner', [
            'before' => $before,
            'after' => $after,
        ], $request);
        Cache::forget('banners.side');
        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công!');
    }

    public function destroy(Banner $banner)
    {
        ActivityLogger::log('banner.delete', $banner, 'Xóa banner', [
            'position' => $banner->position ?? null,
            'title' => $banner->title ?? null,
            'image_path' => $banner->image_path ?? null,
        ]);
        // Xóa file ảnh nếu tồn tại
        if ($banner->image_path) {
            $path = public_path($banner->image_path);
            if (file_exists($path)) @unlink($path);
        }
        $banner->delete();
        Cache::forget('banners.side');
        return redirect()->route('admin.banners.index')->with('success', 'Đã xóa banner!');
    }
}


