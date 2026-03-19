<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'color_id',
        'quantity',
        'price',
        'sale',
        'is_addon',
        'parent_cart_item_id',
        'addon_product_id',
        'addon_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function parent()
    {
        return $this->belongsTo(CartItem::class, 'parent_cart_item_id');
    }
    public function addons()
    {
        return $this->hasMany(CartItem::class, 'parent_cart_item_id');
    }
    public function addonProduct()
    {
        return $this->belongsTo(Product::class, 'addon_product_id');
    }
} 