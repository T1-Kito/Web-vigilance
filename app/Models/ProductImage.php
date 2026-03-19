<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scope để lấy ảnh chính
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Scope để sắp xếp theo thứ tự
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Getter để lấy URL đầy đủ của ảnh
    public function getImageUrlAttribute()
    {
        return asset('images/products/' . $this->image_path);
    }
}
