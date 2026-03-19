<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'slug', 'sort_order', 'banner_image_1', 'banner_image_2', 'promo_banner'];

    protected $casts = [
        'sort_order' => 'int',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function descendantIds(): array
    {
        $ids = [$this->id];
        $this->loadMissing('children');

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }

        return array_values(array_unique($ids));
    }
}
