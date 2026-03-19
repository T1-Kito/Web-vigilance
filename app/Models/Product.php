<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'serial_number',
        'brand',
        'slug',
        'category_id',
        'price',
        'discount_percent',
        'sale',
        'image',
        'description',
        'information',
        'specifications',
        'instruction',
        'is_featured',
        'status',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function addons()
    {
        return $this->hasMany(ProductAddon::class, 'product_id');
    }
    public function addonsWithProduct()
    {
        return $this->hasMany(ProductAddon::class, 'product_id')->with('addonProduct');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->approved()->parentOnly()->latest();
    }
    
    public function allReviews()
    {
        return $this->hasMany(Review::class)->approved()->latest();
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->primary();
    }

    // Giá sau giảm (nếu có giảm giá)
    public function getFinalPriceAttribute()
    {
        if ($this->discount_percent && $this->discount_percent > 0) {
            return round($this->price * (1 - $this->discount_percent / 100), -3); // Làm tròn nghìn
        }
        return $this->price;
    }

    // Có giảm giá không
    public function getHasDiscountAttribute()
    {
        return $this->discount_percent && $this->discount_percent > 0;
    }

    // Scope để lọc sản phẩm có trạng thái hiển thị
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Scope để lọc sản phẩm nổi bật và có trạng thái hiển thị
    public function scopeFeatured($query)
    {
        return $query->where('status', 1)->where('is_featured', 1);
    }
}
