<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'slug' => 'dashboard',
                'name' => 'Tổng quan',
                'sort_order' => 1,
                'items' => [
                    ['slug' => 'admin.access', 'name' => 'Truy cập khu quản trị', 'description' => 'Được phép vào trang quản trị'],
                    ['slug' => 'dashboard.view', 'name' => 'Xem dashboard', 'description' => 'Xem màn hình tổng quan'],
                    ['slug' => 'super.admin', 'name' => 'Toàn quyền hệ thống', 'description' => 'Toàn quyền như super admin'],
                ],
            ],
            [
                'slug' => 'products',
                'name' => 'Sản phẩm',
                'sort_order' => 2,
                'items' => [
                    ['slug' => 'products.view', 'name' => 'Xem sản phẩm'],
                    ['slug' => 'products.create', 'name' => 'Tạo sản phẩm'],
                    ['slug' => 'products.edit', 'name' => 'Sửa sản phẩm'],
                    ['slug' => 'products.delete', 'name' => 'Xóa sản phẩm'],
                    ['slug' => 'products.import', 'name' => 'Nhập Excel sản phẩm'],
                    ['slug' => 'products.export', 'name' => 'Xuất Excel sản phẩm'],
                    ['slug' => 'products.competitor.view', 'name' => 'Xem giá đối thủ'],
                    ['slug' => 'products.competitor.edit', 'name' => 'Cập nhật giá đối thủ'],
                ],
            ],
            [
                'slug' => 'quotations',
                'name' => 'Báo giá',
                'sort_order' => 3,
                'items' => [
                    ['slug' => 'quotation.view', 'name' => 'Xem báo giá'],
                    ['slug' => 'quotation.create', 'name' => 'Tạo báo giá'],
                    ['slug' => 'quotation.edit', 'name' => 'Sửa báo giá'],
                    ['slug' => 'quotation.delete', 'name' => 'Xóa báo giá'],
                    ['slug' => 'quotation.approve', 'name' => 'Duyệt báo giá'],
                    ['slug' => 'quotation.print', 'name' => 'In báo giá'],
                    ['slug' => 'quotation.convert', 'name' => 'Chuyển báo giá sang đơn hàng'],
                ],
            ],
            [
                'slug' => 'orders',
                'name' => 'Đơn hàng',
                'sort_order' => 4,
                'items' => [
                    ['slug' => 'orders.view', 'name' => 'Xem đơn hàng'],
                    ['slug' => 'orders.create', 'name' => 'Tạo đơn hàng'],
                    ['slug' => 'orders.edit', 'name' => 'Sửa đơn hàng'],
                    ['slug' => 'orders.delete', 'name' => 'Xóa đơn hàng'],
                    ['slug' => 'orders.workflow', 'name' => 'Cập nhật quy trình xử lý'],
                    ['slug' => 'orders.payment', 'name' => 'Cập nhật thanh toán'],
                ],
            ],
            [
                'slug' => 'customers',
                'name' => 'Khách hàng',
                'sort_order' => 5,
                'items' => [
                    ['slug' => 'customers.view', 'name' => 'Xem khách hàng'],
                    ['slug' => 'customers.create', 'name' => 'Tạo khách hàng'],
                    ['slug' => 'customers.edit', 'name' => 'Sửa khách hàng'],
                    ['slug' => 'customers.delete', 'name' => 'Xóa khách hàng'],
                    ['slug' => 'customers.import', 'name' => 'Nhập Excel khách hàng'],
                    ['slug' => 'customers.lookup', 'name' => 'Tra cứu khách hàng'],
                ],
            ],
            [
                'slug' => 'documents',
                'name' => 'Chứng từ',
                'sort_order' => 6,
                'items' => [
                    ['slug' => 'deliveries.view', 'name' => 'Xem phiếu xuất kho'],
                    ['slug' => 'deliveries.create', 'name' => 'Tạo phiếu xuất kho'],
                    ['slug' => 'deliveries.delete', 'name' => 'Xóa phiếu xuất kho'],
                    ['slug' => 'invoices.view', 'name' => 'Xem hóa đơn'],
                    ['slug' => 'invoices.create', 'name' => 'Tạo hóa đơn'],
                    ['slug' => 'invoices.delete', 'name' => 'Xóa hóa đơn'],
                    ['slug' => 'purchase-orders.view', 'name' => 'Xem phiếu mua hàng'],
                    ['slug' => 'purchase-orders.create', 'name' => 'Tạo phiếu mua hàng'],
                    ['slug' => 'purchase-orders.edit', 'name' => 'Sửa phiếu mua hàng'],
                    ['slug' => 'purchase-orders.delete', 'name' => 'Xóa phiếu mua hàng'],
                ],
            ],
            [
                'slug' => 'catalog',
                'name' => 'Danh mục & cấu hình',
                'sort_order' => 7,
                'items' => [
                    ['slug' => 'categories.view', 'name' => 'Xem danh mục'],
                    ['slug' => 'categories.create', 'name' => 'Tạo danh mục'],
                    ['slug' => 'categories.edit', 'name' => 'Sửa danh mục'],
                    ['slug' => 'categories.delete', 'name' => 'Xóa danh mục'],
                    ['slug' => 'pricing-formula.edit', 'name' => 'Sửa công thức giá'],
                    ['slug' => 'document-templates.view', 'name' => 'Xem mẫu chứng từ'],
                    ['slug' => 'document-templates.edit', 'name' => 'Sửa mẫu chứng từ'],
                    ['slug' => 'banners.manage', 'name' => 'Quản lý banner'],
                ],
            ],
            [
                'slug' => 'warranty',
                'name' => 'Bảo hành',
                'sort_order' => 8,
                'items' => [
                    ['slug' => 'warranties.view', 'name' => 'Xem bảo hành'],
                    ['slug' => 'warranties.create', 'name' => 'Tạo bảo hành'],
                    ['slug' => 'warranties.edit', 'name' => 'Sửa bảo hành'],
                    ['slug' => 'warranties.delete', 'name' => 'Xóa bảo hành'],
                    ['slug' => 'warranty-claims.view', 'name' => 'Xem yêu cầu bảo hành'],
                    ['slug' => 'warranty-claims.process', 'name' => 'Xử lý yêu cầu bảo hành'],
                ],
            ],
            [
                'slug' => 'system',
                'name' => 'Hệ thống',
                'sort_order' => 9,
                'items' => [
                    ['slug' => 'users.view', 'name' => 'Xem người dùng'],
                    ['slug' => 'users.manage', 'name' => 'Quản lý người dùng'],
                    ['slug' => 'users.permission', 'name' => 'Phân quyền người dùng'],
                    ['slug' => 'activity-logs.view', 'name' => 'Xem nhật ký hoạt động'],
                    ['slug' => 'notifications.manage', 'name' => 'Quản lý thông báo'],
                    ['slug' => 'chat-support.manage', 'name' => 'Quản lý chat hỗ trợ'],
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $group = PermissionGroup::updateOrCreate(
                ['slug' => $groupData['slug']],
                ['name' => $groupData['name'], 'sort_order' => $groupData['sort_order']]
            );

            foreach ($groupData['items'] as $index => $item) {
                Permission::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'permission_group_id' => $group->id,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
