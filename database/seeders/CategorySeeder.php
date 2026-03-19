<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Cấp 1
        $cap1 = [
            ['id' => 56, 'name' => 'MÁY CHẤM CÔNG - KIỂM SOÁT CỬA'],
            ['id' => 57, 'name' => 'HỆ THỐNG POS TÍNH TIỀN'],
            ['id' => 58, 'name' => 'PHỤ KIỆN'],
            ['id' => 59, 'name' => 'CỔNG PHÂN LÀN'],
            ['id' => 60, 'name' => 'CAMERA QUAN SÁT'],
            ['id' => 61, 'name' => 'THIẾT BỊ BÁO ĐỘNG'],
        ];
        foreach ($cap1 as $c1) {
            Category::updateOrCreate([
                'id' => $c1['id']
            ], [
                'name' => $c1['name'],
                'slug' => Str::slug($c1['name']),
                'parent_id' => null
            ]);
        }
        // Cấp 2 & 3
        $cap2 = [
            // Máy chấm công - kiểm soát cửa
            ['id' => 100, 'name' => 'VÂN TAY', 'parent_id' => 56],
            ['id' => 101, 'name' => 'DÙNG THẺ CẢM ỨNG RFID', 'parent_id' => 56],
            ['id' => 102, 'name' => 'NHẬN DẠNG KHUÔN MẶT', 'parent_id' => 56],
            // Hệ thống POS tính tiền
            ['id' => 110, 'name' => 'THIẾT BỊ NGOẠI VI POS', 'parent_id' => 57],
            ['id' => 111, 'name' => 'MÁY IN NHÃN', 'parent_id' => 57],
            ['id' => 112, 'name' => 'MÁY IN HOÁ ĐƠN', 'parent_id' => 57],
            ['id' => 113, 'name' => 'MÁY TÍNH TIỀN FAMETECH INC.(TYSSO)', 'parent_id' => 57],
            // Phụ kiện
            ['id' => 120, 'name' => 'KHOÁ ĐIỆN TỬ', 'parent_id' => 58],
            ['id' => 121, 'name' => 'ĐẦU ĐỌC PHỤ', 'parent_id' => 58],
            ['id' => 122, 'name' => 'NGUỒN CẤP ĐIỆN', 'parent_id' => 58],
            ['id' => 123, 'name' => 'ĐẦU ĐỌC ĐĂNG KÝ', 'parent_id' => 58],
            ['id' => 124, 'name' => 'NÚT NHẤN MỞ CỬA', 'parent_id' => 58],
            ['id' => 125, 'name' => 'CÁC THỨ KHÁC', 'parent_id' => 58],
            // Cổng phân làn
            ['id' => 130, 'name' => 'BARRIER (BÃI XE)', 'parent_id' => 59],
            ['id' => 131, 'name' => 'CỔNG SWING', 'parent_id' => 59],
            ['id' => 132, 'name' => 'CỔNG FLAP', 'parent_id' => 59],
            ['id' => 133, 'name' => 'CỔNG XOAY 3 CÀNG', 'parent_id' => 59],
            // Camera quan sát
            ['id' => 140, 'name' => 'CAMERA WIFI', 'parent_id' => 60],
            ['id' => 141, 'name' => 'CAMERA ĐẶC BIỆT', 'parent_id' => 60],
            ['id' => 142, 'name' => 'CAMERA IP', 'parent_id' => 60],
            // Thiết bị báo động
            ['id' => 150, 'name' => 'RISCO', 'parent_id' => 61],
            ['id' => 151, 'name' => 'MÔ ĐUN MỞ RỘNG - PHỤ KIỆN', 'parent_id' => 61],
        ];
        foreach ($cap2 as $c2) {
            Category::updateOrCreate([
                'id' => $c2['id']
            ], [
                'name' => $c2['name'],
                'slug' => Str::slug($c2['name']),
                'parent_id' => $c2['parent_id']
            ]);
        }
        // Cấp 3 mẫu (bạn bổ sung thêm nếu cần)
        $cap3 = [
            ['id' => 200, 'name' => 'HIVISION', 'parent_id' => 102],
            ['id' => 201, 'name' => 'DAHUA', 'parent_id' => 102],
            ['id' => 202, 'name' => 'VIGILANCE', 'parent_id' => 102],
            ['id' => 210, 'name' => 'CỬA HÀNG', 'parent_id' => 113],
            ['id' => 211, 'name' => 'DỊCH VỤ GIẢI TRÍ', 'parent_id' => 113],
            ['id' => 212, 'name' => 'KHÁCH SẠN', 'parent_id' => 113],
            ['id' => 220, 'name' => 'TỦ TRUNG TÂM', 'parent_id' => 150],
            ['id' => 221, 'name' => 'BÀN ĐIỀU KHIỂN', 'parent_id' => 150],
            ['id' => 222, 'name' => 'CẢM BIẾN CHUYỂN ĐỘNG', 'parent_id' => 150],
        ];
        foreach ($cap3 as $c3) {
            Category::updateOrCreate([
                'id' => $c3['id']
            ], [
                'name' => $c3['name'],
                'slug' => Str::slug($c3['name']),
                'parent_id' => $c3['parent_id']
            ]);
        }
    }
}
