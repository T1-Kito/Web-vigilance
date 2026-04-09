<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductColor;
use App\Models\ProductAddon;
use App\Models\ProductImage;
use App\Support\ActivityLogger;

class ProductController extends Controller
{
    public function lookup(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $lower = static function (string $s): string {
            return mb_strtolower($s, 'UTF-8');
        };

        $normalized = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $q) ?? $q;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $tokens = array_values(array_filter(explode(' ', $normalized), function ($t) {
            return mb_strlen($t) >= 2;
        }));

        $lq = $lower($q);
        $likeFull = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $lq) . '%';

        $products = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'discount_percent', 'serial_number'])
            ->where(function ($query) use ($tokens, $lower, $likeFull) {
                $query->where(function ($q2) use ($likeFull) {
                    $q2->whereRaw('LOWER(name) LIKE ?', [$likeFull])
                        ->orWhereRaw('LOWER(COALESCE(serial_number, "")) LIKE ?', [$likeFull])
                        ->orWhereRaw('LOWER(COALESCE(slug, "")) LIKE ?', [$likeFull]);
                });

                foreach ($tokens as $token) {
                    $lt = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $lower($token)) . '%';
                    $query->orWhere(function ($q3) use ($lt) {
                        $q3->whereRaw('LOWER(name) LIKE ?', [$lt])
                            ->orWhereRaw('LOWER(COALESCE(serial_number, "")) LIKE ?', [$lt])
                            ->orWhereRaw('LOWER(COALESCE(slug, "")) LIKE ?', [$lt]);
                    });
                }
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $items = $products->map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => (string) ($p->name ?? ''),
                'serial_number' => (string) ($p->serial_number ?? ''),
                'price' => (float) ($p->price ?? 0),
                'final_price' => (float) ($p->final_price ?? ($p->price ?? 0)),
            ];
        })->values();

        return response()->json($items);
    }

    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // Tìm kiếm theo tên sản phẩm
        if ($request->filled('search_name')) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }
        
        // Tìm kiếm theo số seri
        if ($request->filled('search_serial')) {
            $query->where('serial_number', 'like', '%' . $request->search_serial . '%');
        }
        
        // Tìm kiếm theo danh mục (lọc đúng theo ID danh mục được chọn)
        if ($request->filled('search_category')) {
            $query->where('category_id', $request->search_category);
        }
        
        // Sắp xếp theo sort_order trước (số nhỏ lên đầu), sau đó mới đến created_at
        $products = $query->orderBy('sort_order', 'asc')->orderByDesc('created_at')->paginate(20)->appends($request->only(['search_name', 'search_serial', 'search_category']));
        $categories = Category::all();
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->normalizeMoneyFields($request);

        if (!$request->filled('price')) {
            $request->merge(['price' => 0]);
        }

        $data = $request->validate([
            'name' => 'required',
            'serial_number' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'unit_name' => 'nullable|string|max:100',
            'origin' => 'nullable|string|max:150',
            'default_warehouse' => 'nullable|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'factory_price' => 'nullable|numeric|min:0',
            'agency_suggested_price' => 'nullable|numeric|min:0',
            'agency_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'labor_price' => 'nullable|numeric|min:0',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'price_includes_tax' => 'nullable|boolean',
            'default_revenue_mode' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'sort_order' => 'nullable|integer|min:1',
            'image' => 'nullable|image',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable',
            'information' => 'nullable',
            'specifications' => 'nullable',
            'instruction' => 'nullable',
            'warranty_months' => 'nullable|integer|min:0|max:240',
            'warranty_content' => 'nullable|string|max:5000',
            'height' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'radius' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ], [
            'name.required' => 'Bạn chưa nhập tên sản phẩm. Vui lòng nhập tên sản phẩm!',
            'category_id.required' => 'Bạn chưa chọn danh mục. Vui lòng chọn danh mục sản phẩm!',
            'category_id.exists' => 'Danh mục bạn chọn không tồn tại. Vui lòng chọn lại!',
            'price.required' => 'Bạn chưa nhập giá sản phẩm. Vui lòng nhập giá!',
            'price.numeric' => 'Giá sản phẩm phải là số. Vui lòng nhập lại!',
            'discount_percent.integer' => 'Phần trăm giảm giá phải là số nguyên!',
            'discount_percent.min' => 'Phần trăm giảm giá không được nhỏ hơn 0%!',
            'discount_percent.max' => 'Phần trăm giảm giá không được lớn hơn 100%!',
            'sort_order.integer' => 'Số thứ tự phải là số nguyên!',
            'sort_order.min' => 'Số thứ tự phải lớn hơn hoặc bằng 1!',
            'image.image' => 'File ảnh không đúng định dạng. Vui lòng chọn file ảnh (jpg, png, gif)!',
            'additional_images.*.image' => 'Một trong các ảnh bổ sung không đúng định dạng!',
            'additional_images.*.mimes' => 'Ảnh bổ sung phải là định dạng jpeg, png, jpg hoặc gif!',
            'additional_images.*.max' => 'Kích thước ảnh bổ sung không được vượt quá 2MB!',
        ]);
        $data['slug'] = Str::slug($data['name']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $data['image'] = $filename;
        }
        $data['price_includes_tax'] = $request->has('price_includes_tax') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['status'] = $request->has('status') ? 1 : 0;
        try {
            $product = Product::create($data);

            ActivityLogger::log('product.create', $product, 'Thêm sản phẩm: ' . ($product->name ?? ''), [
                'name' => $product->name ?? null,
                'category_id' => $product->category_id ?? null,
                'price' => $product->price ?? null,
            ], $request);
            
            // Lưu ảnh bổ sung
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $index => $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images/products'), $filename);
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $filename,
                        'alt_text' => $product->name . ' - Ảnh ' . ($index + 1),
                        'sort_order' => $index + 1,
                        'is_primary' => false,
                    ]);
                }
            }
            
            // Lưu màu sắc
            if ($request->has('colors')) {
                foreach ($request->input('colors') as $color) {
                    if (!empty($color['color_name'])) {
                        ProductColor::create([
                            'product_id' => $product->id,
                            'color_name' => $color['color_name'],
                            'color_code' => $color['color_code'] ?? null,
                            'price' => $color['price'] ?? null,
                            'quantity' => $color['quantity'] ?? 0,
                        ]);
                    }
                }
            }
            // Lưu addon
            if ($request->has('addons')) {
                foreach ($request->input('addons') as $addon) {
                    if (!empty($addon['addon_id'])) {
                        $addonData = [
                            'product_id' => $product->id,
                            'addon_id' => $addon['addon_id'],
                            'addon_price' => $addon['addon_price'] ?? 0,
                            'description' => $addon['description'] ?? null,
                        ];
                        if (isset($addon['image']) && $addon['image']) {
                            $file = $addon['image'];
                            $filename = time().'_addon_'.uniqid().'.'.$file->getClientOriginalExtension();
                            $file->move(public_path('images/products'), $filename);
                            $addonData['image'] = $filename;
                        }
                        ProductAddon::create($addonData);
                    }
                }
            }
        return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm thành công!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Lỗi unique
                return back()->withInput()->with('error', 'Tên sản phẩm hoặc đường dẫn đã tồn tại, vui lòng chọn tên khác!');
            }
            throw $e;
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('images');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->normalizeMoneyFields($request);

        $before = $product->only(['name', 'price', 'discount_percent', 'sort_order', 'status', 'is_featured', 'category_id', 'brand', 'serial_number']);
        if (!$request->filled('price')) {
            $request->merge(['price' => 0]);
        }

        $data = $request->validate([
            'name' => 'required',
            'serial_number' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'unit_name' => 'nullable|string|max:100',
            'origin' => 'nullable|string|max:150',
            'default_warehouse' => 'nullable|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'factory_price' => 'nullable|numeric|min:0',
            'agency_suggested_price' => 'nullable|numeric|min:0',
            'agency_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'labor_price' => 'nullable|numeric|min:0',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'price_includes_tax' => 'nullable|boolean',
            'default_revenue_mode' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'sort_order' => 'nullable|integer|min:1',
            'image' => 'nullable|image',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable',
            'information' => 'nullable',
            'specifications' => 'nullable',
            'instruction' => 'nullable',
            'warranty_months' => 'nullable|integer|min:0|max:240',
            'warranty_content' => 'nullable|string|max:5000',
            'height' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'radius' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ], [
            'name.required' => 'Bạn chưa nhập tên sản phẩm. Vui lòng nhập tên sản phẩm!',
            'category_id.required' => 'Bạn chưa chọn danh mục. Vui lòng chọn danh mục sản phẩm!',
            'category_id.exists' => 'Danh mục bạn chọn không tồn tại. Vui lòng chọn lại!',
            'price.required' => 'Bạn chưa nhập giá sản phẩm. Vui lòng nhập giá!',
            'price.numeric' => 'Giá sản phẩm phải là số. Vui lòng nhập lại!',
            'discount_percent.integer' => 'Phần trăm giảm giá phải là số nguyên!',
            'discount_percent.min' => 'Phần trăm giảm giá không được nhỏ hơn 0%!',
            'discount_percent.max' => 'Phần trăm giảm giá không được lớn hơn 100%!',
            'sort_order.integer' => 'Số thứ tự phải là số nguyên!',
            'sort_order.min' => 'Số thứ tự phải lớn hơn hoặc bằng 1!',
            'image.image' => 'File ảnh không đúng định dạng. Vui lòng chọn file ảnh (jpg, png, gif)!',
            'additional_images.*.image' => 'Một trong các ảnh bổ sung không đúng định dạng!',
            'additional_images.*.mimes' => 'Ảnh bổ sung phải là định dạng jpeg, png, jpg hoặc gif!',
            'additional_images.*.max' => 'Kích thước ảnh bổ sung không được vượt quá 2MB!',
        ]);
        $data['slug'] = Str::slug($data['name']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $data['image'] = $filename;
        }
        $data['price_includes_tax'] = $request->has('price_includes_tax') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['status'] = $request->has('status') ? 1 : 0;
        $product->update($data);

        $after = $product->fresh()->only(['name', 'price', 'discount_percent', 'sort_order', 'status', 'is_featured', 'category_id', 'brand', 'serial_number']);
        ActivityLogger::log('product.update', $product, 'Cập nhật sản phẩm: ' . ($product->name ?? ''), [
            'before' => $before,
            'after' => $after,
        ], $request);
        
        // Lưu ảnh bổ sung
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/products'), $filename);
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $filename,
                    'alt_text' => $product->name . ' - Ảnh ' . ($index + 1),
                    'sort_order' => ProductImage::where('product_id', $product->id)->max('sort_order') + 1,
                    'is_primary' => false,
                ]);
            }
        }
        // Cập nhật màu sắc
        $colorInputs = $request->input('colors', []);
        $colorIds = [];
        foreach ($colorInputs as $key => $color) {
            if ($key === 'new') {
                // Thêm màu mới
                foreach (($color['color_name'] ?? []) as $i => $name) {
                    if (!empty($name)) {
                        $newColor = ProductColor::create([
                            'product_id' => $product->id,
                            'color_name' => $name,
                            'color_code' => $color['color_code'][$i] ?? null,
                            'price' => $color['price'][$i] ?? null,
                            'quantity' => $color['quantity'][$i] ?? 0,
                        ]);
                        $colorIds[] = $newColor->id;
                    }
                }
            } else {
                // Sửa màu cũ
                $productColor = ProductColor::find($key);
                if ($productColor && $productColor->product_id == $product->id) {
                    $productColor->update([
                        'color_name' => $color['color_name'],
                        'color_code' => $color['color_code'] ?? null,
                        'price' => $color['price'] ?? null,
                        'quantity' => $color['quantity'] ?? 0,
                    ]);
                    $colorIds[] = $productColor->id;
                }
            }
        }
        // Xóa màu không còn trong form
        $product->colors()->whereNotIn('id', $colorIds)->delete();

        // Cập nhật addon (sản phẩm mua kèm)
        $addonInputs = $request->input('addons', []);
        $addonIds = [];
        foreach ($addonInputs as $key => $addon) {
            if ($key === 'new') {
                // Thêm addon mới
                foreach (($addon['addon_product_id'] ?? []) as $i => $addon_product_id) {
                    if (!empty($addon_product_id)) {
                        $newAddon = \App\Models\ProductAddon::create([
                            'product_id' => $product->id,
                            'addon_product_id' => $addon_product_id,
                            'addon_price' => $addon['addon_price'][$i] ?? null,
                            'discount_percent' => $addon['discount_percent'][$i] ?? null,
                            'description' => $addon['description'][$i] ?? null,
                        ]);
                        $addonIds[] = $newAddon->id;
                    }
                }
            } else {
                // Sửa addon cũ
                $productAddon = \App\Models\ProductAddon::find($key);
                if ($productAddon && $productAddon->product_id == $product->id) {
                    $productAddon->update([
                        'addon_product_id' => $addon['addon_product_id'] ?? $productAddon->addon_product_id,
                        'addon_price' => $addon['addon_price'] ?? $productAddon->addon_price,
                        'discount_percent' => $addon['discount_percent'] ?? $productAddon->discount_percent,
                        'description' => $addon['description'] ?? $productAddon->description,
                    ]);
                    $addonIds[] = $productAddon->id;
                }
            }
        }
        // Xóa addon không còn trong form
        $product->addons()->whereNotIn('id', $addonIds)->delete();

        $returnUrl = $request->input('return_url');
        if (is_string($returnUrl) && $returnUrl !== '') {
            $baseUrl = url('/');
            if (str_starts_with($returnUrl, $baseUrl)) {
                if (!str_contains($returnUrl, '#')) {
                    $returnUrl .= '#product-' . $product->id;
                }
                return redirect()->to($returnUrl)->with('success', 'Cập nhật sản phẩm thành công!');
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function deleteAdditionalImage(Request $request, Product $product)
    {
        try {
            $imageId = $request->input('image_id');
            
            if (!$imageId) {
                return response()->json(['success' => false, 'message' => 'Thiếu ID ảnh!'], 400);
            }
            
            $image = \App\Models\ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->first();
            
            if (!$image) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy ảnh!'], 404);
            }
            
            // Xóa file ảnh từ storage
            $imagePath = public_path('images/products/' . $image->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Xóa record từ database
            $image->delete();
            
            \Log::info('Đã xóa ảnh thành công', [
                'image_id' => $imageId,
                'product_id' => $product->id,
                'image_path' => $image->image_path
            ]);
            
            return response()->json(['success' => true, 'message' => 'Đã xóa ảnh thành công!']);
            
        } catch (\Exception $e) {
            \Log::error('Lỗi khi xóa ảnh', [
                'image_id' => $request->input('image_id'),
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa ảnh: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Product $product)
    {
        ActivityLogger::log('product.delete', $product, 'Xóa sản phẩm: ' . ($product->name ?? ''), [
            'name' => $product->name ?? null,
        ]);
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Đã xóa sản phẩm!');
    }

    public function show(Product $product)
    {
        $product->load('images');

        $quoteItems = \App\Models\QuoteItem::query()
            ->with(['quote'])
            ->where('product_id', $product->id)
            ->whereHas('quote')
            ->latest('id')
            ->get();

        $salesOrderItems = \App\Models\SalesOrderItem::query()
            ->with(['salesOrder'])
            ->where('product_id', $product->id)
            ->whereHas('salesOrder')
            ->latest('id')
            ->get();

        $invoiceItems = \App\Models\InvoiceItem::query()
            ->with(['invoice'])
            ->where('product_id', $product->id)
            ->whereHas('invoice')
            ->latest('id')
            ->get();

        $quoteCount = $quoteItems->pluck('quote_id')->filter()->unique()->count();
        $salesOrderCount = $salesOrderItems->pluck('sales_order_id')->filter()->unique()->count();
        $invoiceCount = $invoiceItems->pluck('invoice_id')->filter()->unique()->count();

        return view('admin.products.show', compact(
            'product',
            'quoteItems',
            'salesOrderItems',
            'invoiceItems',
            'quoteCount',
            'salesOrderCount',
            'invoiceCount'
        ));
    }



    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
        $rows = $data[0];
        // Tìm dòng đầu tiên không rỗng làm header
        $headerRowIndex = 0;
        foreach ($rows as $i => $row) {
            if (!empty(array_filter($row))) {
                $headerRowIndex = $i;
                break;
            }
        }
        $header = array_map(function($h) { return trim($h); }, array_slice($rows[$headerRowIndex], 1));
        \Log::info('First row (rows[headerRowIndex])', $rows[$headerRowIndex]);
        unset($rows[$headerRowIndex]);

        $importedCount = 0;
        $errors = [];
        
        // Mapping header, chấp nhận cả tên cột có dấu cách cuối
        $headerMap = [
            'DANH MỤC CẤP 1' => 'cat1',
            'DANH MỤC CẤP 2' => 'cat2',
            'DANH MỤC CẤP 2 ' => 'cat2',
            'Danh Mục cấp 3' => 'cat3',
            'TÊN SẢN PHẨM' => 'name',
            'TÊN SẢN PHẨM ' => 'name',
            'MÃ SẢN PHẨM' => 'serial_number',
            'MÃ SẢN PHẨM ' => 'serial_number',
            'SỐ SERI (SN)' => 'serial_number',
            'SỐ SERI (SN) ' => 'serial_number',
            'SỐ SERI' => 'serial_number',
            'GIÁ BÁN' => 'price',
            'GIÁ KHUYẾN MÃI' => 'sale',
            'MÔ TẢ' => 'description',
            'MÔ TẢ ' => 'description',
            'THÔNG TIN SẢN PHẨM' => 'information',
            'THÔNG TIN SẢN PHẨM ' => 'information',
            'THÔNG SỐ KỸ THUẬT' => 'specifications',
            'THÔNG SỐ KỸ THUẬT ' => 'specifications',
            'HƯỚNG DẪN SỬ DỤNG' => 'instruction',
            'HƯỚNG DẪN SỬ DỤNG ' => 'instruction',
            'ẢNH' => 'image',
            'ẢNH ' => 'image',
            'image' => 'image',
        ];

        foreach ($rows as $index => $row) {
            // Bỏ cột STT ở đầu mỗi dòng
            $row = array_slice($row, 1);
            // Check if row is empty
            if (empty(array_filter($row))) {
                continue;
            }

            \Log::info('Header', $header);
            \Log::info('Raw row', $row);

            $rowData = [];
            foreach ($headerMap as $excelHeader => $dbField) {
                $headerIndex = array_search($excelHeader, $header);
                if ($headerIndex !== false) {
                    $rowData[$dbField] = isset($row[$headerIndex]) ? trim($row[$headerIndex]) : null;
                }
            }

            \Log::info('Import row', $rowData);
            \Log::info('Header', $header);
            \Log::info('RowData', $rowData);

            $category = null;
            // Prioritize finding the most specific category that exists
            if (!empty($rowData['cat3'])) {
                $category = Category::find($rowData['cat3']);
            }
            if (!$category && !empty($rowData['cat2'])) {
                $category = Category::find($rowData['cat2']);
            }
            if (!$category && !empty($rowData['cat1'])) {
                $category = Category::find($rowData['cat1']);
            }

            if (!$category) {
                \Log::warning('Không tìm thấy danh mục', $rowData);
                $errors[] = "Dòng " . ($index + 2) . ": Không tìm thấy danh mục hợp lệ.";
                continue;
            }
            
            // Chuẩn hoá giá trị số
            $priceRaw = $rowData['price'] ?? null;
            $saleRaw = $rowData['sale'] ?? null;
            $price = is_numeric($priceRaw) ? (float)$priceRaw : (float)preg_replace('/[^0-9]/', '', (string)$priceRaw);
            $sale = is_numeric($saleRaw) ? (float)$saleRaw : ($saleRaw !== null ? (float)preg_replace('/[^0-9]/', '', (string)$saleRaw) : null);

            Product::create([
                'name' => $rowData['name'] ?? 'Sản phẩm chưa đặt tên',
                'serial_number' => $rowData['serial_number'] ?? null,
                'price' => $price,
                'sale' => $sale,
                'description' => $rowData['description'] ?? null,
                'information' => $rowData['information'] ?? null,
                'specifications' => $rowData['specifications'] ?? null,
                'instruction' => $rowData['instruction'] ?? null,
                'category_id' => $category->id,
                'slug' => Str::slug($rowData['name'] ?? ('san-pham-' . uniqid())),
                'status' => 1,
                'image' => $rowData['image'] ?? null,
            ]);
            $importedCount++;
        }

        if (!empty($errors)) {
            return back()->with('error', "Đã import {$importedCount} sản phẩm. Lỗi: " . implode('; ', $errors));
        }

        return back()->with('success', "Đã import thành công {$importedCount} sản phẩm!");
    }

    public function exportExcel()
    {
        $products = \App\Models\Product::with('category')->get();
        $data = [];
        $header = [
            'STT',
            'DANH MỤC CẤP 1',
            'DANH MỤC CẤP 2',
            'Danh Mục cấp 3',
            'TÊN SẢN PHẨM',
            'SỐ SERI (SN)',
            'GIÁ BÁN',
            'GIÁ KHUYẾN MÃI',
            'MÔ TẢ',
            'THÔNG TIN SẢN PHẨM',
            'THÔNG SỐ KỸ THUẬT',
            'HƯỚNG DẪN SỬ DỤNG',
            'image',
        ];
        $data[] = $header;
        foreach ($products as $i => $product) {
            // Lấy id danh mục các cấp
            $cat3 = $product->category_id;
            $cat2 = $product->category && $product->category->parent ? $product->category->parent->id : '';
            $cat1 = $product->category && $product->category->parent && $product->category->parent->parent ? $product->category->parent->parent->id : '';
            $data[] = [
                $i + 1,
                $cat1,
                $cat2,
                $cat3,
                $product->name,
                $product->serial_number,
                $product->price,
                $product->sale,
                $product->description,
                $product->information,
                $product->specifications,
                $product->instruction,
                $product->image,
            ];
        }
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SimpleArrayExport($data), 'products_export.xlsx');
    }

    /**
     * JSON: lịch sử thay đổi sản phẩm (từ activity_logs — before/after khi cập nhật).
     */
    private function normalizeMoneyFields(Request $request): void
    {
        $moneyFields = [
            'price',
            'factory_price',
            'agency_suggested_price',
            'agency_price',
            'retail_price',
            'shipping_price',
            'labor_price',
            'cost_price',
        ];

        $normalized = [];
        foreach ($moneyFields as $field) {
            $value = $request->input($field);
            if ($value === null || $value === '') {
                continue;
            }

            if (is_string($value)) {
                $value = str_replace(['.', ',', ' '], '', $value);
            }

            $normalized[$field] = is_numeric($value) ? $value : preg_replace('/[^0-9]/', '', (string) $value);
        }

        if ($normalized !== []) {
            $request->merge($normalized);
        }
    }

    public function activityHistory(Product $product)
    {
        $categoryNames = Category::query()->pluck('name', 'id')->all();

        $logs = ActivityLog::query()
            ->where('subject_type', Product::class)
            ->where('subject_id', $product->id)
            ->whereIn('action', ['product.update', 'product.create'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $items = [];
        foreach ($logs as $log) {
            $props = is_array($log->properties) ? $log->properties : [];
            $row = [
                'id' => $log->id,
                'action' => $log->action,
                'at' => $log->created_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i'),
                'user_email' => $log->user_email ?? '',
                'description' => $log->description ?? '',
                'changes' => [],
            ];

            if ($log->action === 'product.update' && isset($props['before'], $props['after']) && is_array($props['before']) && is_array($props['after'])) {
                $row['changes'] = $this->diffProductSnapshots($props['before'], $props['after'], $categoryNames);
            } elseif ($log->action === 'product.create' && $props !== []) {
                $row['changes'] = $this->productCreateSnapshotRows($props, $categoryNames);
            }

            $items[] = $row;
        }

        return response()->json([
            'ok' => true,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'items' => $items,
        ]);
    }

    /**
     * @return array<int, array{label: string, from: string, to: string}>
     */
    private function diffProductSnapshots(array $before, array $after, array $categoryNames): array
    {
        $keys = [
            'name' => 'Tên sản phẩm',
            'brand' => 'Hãng',
            'serial_number' => 'Mã sản phẩm',
            'price' => 'Giá bán',
            'discount_percent' => 'Giảm giá (%)',
            'category_id' => 'Danh mục',
            'status' => 'Hiển thị',
            'is_featured' => 'Nổi bật',
            'sort_order' => 'Thứ tự hiển thị',
        ];

        $out = [];
        foreach ($keys as $key => $label) {
            $b = $before[$key] ?? null;
            $a = $after[$key] ?? null;
            if ($this->productFieldEquals($key, $b, $a)) {
                continue;
            }
            $out[] = [
                'label' => $label,
                'from' => $this->formatProductFieldForHistory($key, $b, $categoryNames),
                'to' => $this->formatProductFieldForHistory($key, $a, $categoryNames),
            ];
        }

        return $out;
    }

    private function productFieldEquals(string $key, $b, $a): bool
    {
        if ($key === 'price') {
            return round((float) $b, 2) === round((float) $a, 2);
        }

        return $b == $a;
    }

    private function formatProductFieldForHistory(string $key, $value, array $categoryNames): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        switch ($key) {
            case 'price':
                return number_format((float) $value, 0, ',', '.') . 'đ';
            case 'status':
                return ((int) $value) === 1 ? 'Hiện' : 'Ẩn';
            case 'is_featured':
                return ((int) $value) === 1 ? 'Có' : 'Không';
            case 'category_id':
                $id = (int) $value;

                return $categoryNames[$id] ?? ('#' . $id);
            case 'discount_percent':
                return (string) $value . '%';
            default:
                return (string) $value;
        }
    }

    /**
     * @return array<int, array{label: string, from: string, to: string}>
     */
    private function productCreateSnapshotRows(array $props, array $categoryNames): array
    {
        $out = [];
        if (!empty($props['name'])) {
            $out[] = ['label' => 'Tên sản phẩm', 'from' => '—', 'to' => (string) $props['name']];
        }
        if (isset($props['category_id'])) {
            $cid = (int) $props['category_id'];
            $out[] = [
                'label' => 'Danh mục',
                'from' => '—',
                'to' => $categoryNames[$cid] ?? ('#' . $cid),
            ];
        }
        if (isset($props['price']) && $props['price'] !== '' && $props['price'] !== null) {
            $out[] = [
                'label' => 'Giá bán',
                'from' => '—',
                'to' => number_format((float) $props['price'], 0, ',', '.') . 'đ',
            ];
        }

        return $out;
    }
}
