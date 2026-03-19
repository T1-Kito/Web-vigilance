<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddon extends Model
{
    protected $fillable = [
        'product_id',
        'addon_product_id',
        'addon_price',
        'description',
        'discount_percent',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function addonProduct()
    {
        return $this->belongsTo(Product::class, 'addon_product_id');
}
} 