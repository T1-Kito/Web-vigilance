# Hệ Thống Yêu Cầu Bảo Hành

## Tổng Quan
Hệ thống yêu cầu bảo hành cho phép khách hàng tạo yêu cầu bảo hành trực tuyến thông qua việc nhập số seri sản phẩm.

## Cấu Trúc Database

### 1. Bảng `warranties`
- Lưu thông tin bảo hành sản phẩm
- Chứa thông tin khách hàng, ngày mua, thời hạn bảo hành

### 2. Bảng `warranty_claims`
- Lưu các yêu cầu bảo hành
- Chứa thông tin lỗi, khiếu nại, trạng thái xử lý

### 3. Bảng `warranty_statuses`
- Lưu lịch sử thay đổi trạng thái bảo hành
- Theo dõi quá trình xử lý yêu cầu

## Luồng Hoạt Động

### 1. Khách hàng kiểm tra bảo hành
- Truy cập `/warranty/check`
- Nhập số seri sản phẩm
- Hệ thống hiển thị thông tin bảo hành

### 2. Tạo yêu cầu bảo hành
- Nếu bảo hành còn hiệu lực, khách hàng có thể tạo yêu cầu
- Điền thông tin lỗi và khiếu nại
- Hệ thống tự động tạo số yêu cầu (WC + ngày + số thứ tự)

### 3. Admin xử lý yêu cầu
- Admin có thể xem danh sách yêu cầu bảo hành
- Cập nhật trạng thái: pending → approved → in_progress → completed
- Thêm ghi chú và thông tin sửa chữa

## Các Model Chính

### WarrantyController
- `check()`: Hiển thị form kiểm tra bảo hành
- `search()`: Tìm kiếm bảo hành theo số seri
- `claim()`: Xử lý tạo yêu cầu bảo hành

### Warranty Model
- `canClaimWarranty()`: Kiểm tra có thể tạo yêu cầu không
- `updateStatus()`: Cập nhật trạng thái bảo hành
- `claims()`: Relationship với warranty_claims

### WarrantyClaim Model
- `updateStatus()`: Cập nhật trạng thái yêu cầu
- `markAsCompleted()`: Đánh dấu hoàn thành
- Các accessor cho hiển thị trạng thái

## Routes

```php
// Frontend routes
Route::get('/warranty/check', [WarrantyController::class, 'check'])->name('warranty.check');
Route::post('/warranty/search', [WarrantyController::class, 'search'])->name('warranty.search');
Route::post('/warranty/claim', [WarrantyController::class, 'claim'])->name('warranty.claim');

// Admin routes
Route::get('warranties/claims', [Admin\WarrantyController::class, 'claims'])->name('warranties.claims');
Route::patch('warranties/claims/{claim}/status', [Admin\WarrantyController::class, 'updateClaimStatus'])->name('warranties.claims.update-status');
```

## Validation Rules

### Tạo yêu cầu bảo hành
```php
'warranty_id' => 'required|exists:warranties,id',
'issue_description' => 'required|string|max:1000',
'customer_complaint' => 'required|string|max:1000',
'customer_name' => 'required|string|max:255',
'customer_phone' => 'required|string|max:20',
'customer_email' => 'nullable|email|max:255'
```

## Trạng Thái Yêu Cầu Bảo Hành

1. **pending**: Chờ xử lý
2. **approved**: Đã duyệt
3. **rejected**: Từ chối
4. **in_progress**: Đang sửa chữa
5. **completed**: Hoàn thành

## Tính Năng Đặc Biệt

### Tự động tạo số yêu cầu
- Format: `WC` + `YYYYMMDD` + `0001` (số thứ tự trong ngày)
- Ví dụ: `WC202507300001`

### Log thay đổi trạng thái
- Mỗi khi thay đổi trạng thái, hệ thống tự động log vào bảng `warranty_statuses`
- Lưu thông tin người thay đổi và ghi chú

### Kiểm tra hiệu lực bảo hành
- Tự động kiểm tra ngày hết hạn bảo hành
- Chỉ cho phép tạo yêu cầu khi bảo hành còn hiệu lực

## Bảo Trì Và Cập Nhật

### Thêm trạng thái mới
1. Cập nhật migration `warranty_claims` table
2. Thêm vào enum `claim_status`
3. Cập nhật `getStatusTextAttribute()` và `getStatusColorAttribute()` trong WarrantyClaim model

### Thêm trường mới
1. Tạo migration mới
2. Cập nhật `$fillable` trong model
3. Cập nhật validation rules
4. Cập nhật form và view

### Thêm tính năng mới
1. Tạo method trong controller
2. Định nghĩa route
3. Tạo view nếu cần
4. Cập nhật documentation 