<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'parent_id',
        'rating',
        'performance_rating',
        'durability_rating',
        'content',
        'images',
        'is_purchased',
        'is_approved',
    ];

    protected $casts = [
        'images' => 'array',
        'is_purchased' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function parent()
    {
        return $this->belongsTo(Review::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_id')->approved()->latest();
    }

    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_id');
    }
}
