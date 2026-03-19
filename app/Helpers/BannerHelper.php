<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BannerHelper
{
    /**
     * Kiểm tra và trả về đường dẫn ảnh banner
     */
    public static function getBannerImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }

        // Nếu là URL tuyệt đối
        if (str_starts_with($imagePath, ['http://', 'https://'])) {
            return $imagePath;
        }

        // Xử lý đường dẫn tương đối
        $cleanPath = ltrim($imagePath, '/');
        
        // Kiểm tra file có tồn tại không
        $fullPath = public_path($cleanPath);
        $fileExists = file_exists($fullPath);
        
        // Log để debug
        Log::info('BannerHelper::getBannerImageUrl', [
            'original_path' => $imagePath,
            'clean_path' => $cleanPath,
            'full_path' => $fullPath,
            'file_exists' => $fileExists,
            'public_path' => public_path(),
            'current_url' => request()->url()
        ]);
        
        // Trả về đường dẫn tương đối từ public folder
        return '/' . $cleanPath;
    }
    
    /**
     * Kiểm tra xem banner có hiển thị được không
     */
    public static function canDisplayBanner($banner)
    {
        if (!$banner->image_path) {
            return false;
        }
        
        $imageUrl = self::getBannerImageUrl($banner->image_path);
        $fullPath = public_path(ltrim($banner->image_path, '/'));
        
        return [
            'can_display' => file_exists($fullPath),
            'image_url' => $imageUrl,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
        ];
    }
}
