<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Product extends Model
{
    protected $fillable = [
        'name',
        'serial_number',
        'brand',
        'unit_name',
        'origin',
        'default_warehouse',
        'slug',
        'category_id',
        'price',
        'factory_price',
        'agency_suggested_price',
        'agency_price',
        'retail_price',
        'shipping_price',
        'labor_price',
        'vat_percent',
        'price_includes_tax',
        'default_revenue_mode',
        'cost_price',
        'discount_percent',
        'competitor_source',
        'competitor_price',
        'competitor_product_url',
        'competitor_checked_at',
        'sale',
        'image',
        'description',
        'information',
        'specifications',
        'instruction',
        'warranty_months',
        'warranty_content',
        'height',
        'length',
        'width',
        'radius',
        'weight',
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

    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class)
            ->orderBy('from_qty')
            ->orderBy('priority');
    }

    public function activePriceTiers()
    {
        return $this->hasMany(ProductPriceTier::class)
            ->where('is_active', true)
            ->orderBy('from_qty')
            ->orderBy('priority');
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

    public function resolveUnitPriceByQuantity(int $quantity, ?string $customerType = null): float
    {
        $detail = $this->resolveUnitPriceDetailByQuantity($quantity, $customerType);

        return (float) ($detail['final_price'] ?? 0);
    }

    public function resolveUnitPriceDetailByQuantity(int $quantity, ?string $customerType = null): array
    {
        $qty = max(1, $quantity);
        $base = (float) ($this->final_price ?? $this->price ?? 0);
        $normalizedType = $customerType ? trim(mb_strtolower($customerType, 'UTF-8')) : null;

        /** @var Collection<int, ProductPriceTier> $tiers */
        $tiers = $this->relationLoaded('priceTiers')
            ? $this->priceTiers->where('is_active', true)
            : $this->activePriceTiers()->get();

        $matchedByType = $tiers->first(function (ProductPriceTier $t) use ($qty, $normalizedType) {
            if (!(bool) $t->is_active || !$t->appliesToQuantity($qty)) {
                return false;
            }

            $tierType = $t->customer_type ? trim(mb_strtolower((string) $t->customer_type, 'UTF-8')) : 'all';

            return $normalizedType && $tierType === $normalizedType;
        });

        $tier = $matchedByType ?: $tiers->first(function (ProductPriceTier $t) use ($qty) {
            if (!(bool) $t->is_active || !$t->appliesToQuantity($qty)) {
                return false;
            }

            $tierType = $t->customer_type ? trim(mb_strtolower((string) $t->customer_type, 'UTF-8')) : 'all';

            return $tierType === 'all';
        });

        if (!$tier) {
            $fallbackPrice = $base;
            if ($normalizedType === 'factory' && (float) ($this->factory_price ?? 0) > 0) {
                $fallbackPrice = (float) $this->factory_price;
            } elseif ($normalizedType === 'agent' && (float) ($this->agency_price ?? 0) > 0) {
                $fallbackPrice = (float) $this->agency_price;
            } elseif ($normalizedType === 'retail' && (float) ($this->retail_price ?? 0) > 0) {
                $fallbackPrice = (float) $this->retail_price;
            }

            $source = 'base_price';
            if ($normalizedType === 'factory' && (float) ($this->factory_price ?? 0) > 0) {
                $source = 'factory_price';
            } elseif ($normalizedType === 'agent' && (float) ($this->agency_price ?? 0) > 0) {
                $source = 'agency_price';
            } elseif ($normalizedType === 'retail' && (float) ($this->retail_price ?? 0) > 0) {
                $source = 'retail_price';
            }

            return [
                'base_price' => round($base, 2),
                'final_price' => round($fallbackPrice, 2),
                'tier_applied' => round($fallbackPrice, 2) !== round($base, 2),
                'price_source' => $source,
                'tier' => null,
            ];
        }

        $final = round($tier->resolveUnitPrice($base), 2);

        return [
            'base_price' => round($base, 2),
            'final_price' => $final,
            'tier_applied' => round($final, 2) !== round($base, 2),
            'price_source' => $matchedByType ? 'tier_exact' : 'tier_all',
            'tier' => [
                'id' => $tier->id,
                'customer_type' => (string) ($tier->customer_type ?? 'all'),
                'from_qty' => (int) $tier->from_qty,
                'to_qty' => $tier->to_qty !== null ? (int) $tier->to_qty : null,
                'pricing_type' => (string) $tier->pricing_type,
                'price_value' => $tier->price_value !== null ? (float) $tier->price_value : null,
                'percent_value' => $tier->percent_value !== null ? (float) $tier->percent_value : null,
            ],
        ];
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
