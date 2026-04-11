<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceTier extends Model
{
    protected $fillable = [
        'product_id',
        'from_qty',
        'to_qty',
        'customer_type',
        'pricing_type',
        'price_value',
        'percent_value',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'from_qty' => 'integer',
        'to_qty' => 'integer',
        'price_value' => 'decimal:2',
        'percent_value' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function appliesToQuantity(int $quantity): bool
    {
        if ($quantity < (int) $this->from_qty) {
            return false;
        }

        if ($this->to_qty !== null && $quantity > (int) $this->to_qty) {
            return false;
        }

        return true;
    }

    public function resolveUnitPrice(float $basePrice): float
    {
        if ($this->pricing_type === 'fixed') {
            return max(0, (float) ($this->price_value ?? 0));
        }

        if ($this->pricing_type === 'percent_discount') {
            $percent = max(0, min(100, (float) ($this->percent_value ?? 0)));
            return max(0, $basePrice * (1 - ($percent / 100)));
        }

        return $basePrice;
    }
}
