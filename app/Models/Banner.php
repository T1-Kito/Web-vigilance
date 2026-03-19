<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'position',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getIsVideoAttribute(): bool
    {
        $path = (string) ($this->image_path ?? '');
        if ($path === '') {
            return false;
        }
        $path = strtolower(parse_url($path, PHP_URL_PATH) ?: $path);
        return str_ends_with($path, '.mp4') || str_ends_with($path, '.webm');
    }

    public function getMediaUrlAttribute(): string
    {
        // Back-compat: media url == image url (image_path can be video too)
        return $this->image_url;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Computed full URL for the image, tolerant to legacy values (filename only)
     */
    public function getImageUrlAttribute(): string
    {
        $path = $this->image_path ?? '';
        if ($path === '') {
            return '';
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        
        // Sửa đường dẫn cho Vinahost
        if (!str_contains($path, '/')) {
            $path = 'images/banners/' . $path;
        }
        
        // Tạo URL hợp lệ tương ứng với vị trí thật của file trên server, kể cả khi app chạy trong subfolder
        $relativePath = '/' . ltrim($path, '/');

        // Đường dẫn tuyệt đối nơi Laravel sẽ lưu file (public_path điểm tới public của app)
        $absoluteInAppPublic = public_path(ltrim($path, '/'));
        if (file_exists($absoluteInAppPublic)) {
            // Tính prefix giữa DocumentRoot và public_path (ví dụ: webCN-laravel/public)
            $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : '';
            $appPublicPath = rtrim(public_path(), '/\\');

            $prefix = '';
            if ($documentRoot !== '' && str_starts_with($appPublicPath, $documentRoot)) {
                $prefix = trim(str_replace('\\', '/', substr($appPublicPath, strlen($documentRoot))), '/');
            }

            if ($prefix !== '') {
                return '/' . $prefix . $relativePath; // e.g. /webCN-laravel/public/images/banners/...
            }

            // Không xác định được prefix → trả về đường dẫn tương đối từ domain
            return $relativePath;
        }

        // File không nằm trong public của app → thử ngay dưới DocumentRoot/images/banners
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : '';
        if ($documentRoot !== '') {
            $absoluteInDocRoot = $documentRoot . $relativePath;
            if (file_exists($absoluteInDocRoot)) {
                return $relativePath; // /images/banners/...
            }
        }

        // Fallback cuối: tôn trọng cấu hình ASSET_URL nếu có
        return asset($path);
    }
}


