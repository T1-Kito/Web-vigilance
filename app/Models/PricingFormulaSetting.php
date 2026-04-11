<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PricingFormulaSetting extends Model
{
    protected $fillable = [
        'list_multiplier',
        'retail_discount_percent',
        'agent_markup_1_5_percent',
        'agent_markup_6_10_percent',
        'agent_markup_over_10_percent',
        'updated_by',
    ];

    public static function current(): self
    {
        $defaults = [
            'list_multiplier' => 2.0,
            'retail_discount_percent' => 15.0,
            'agent_markup_1_5_percent' => 30.0,
            'agent_markup_6_10_percent' => 25.0,
            'agent_markup_over_10_percent' => 15.0,
        ];

        if (!Schema::hasTable('pricing_formula_settings')) {
            return new static($defaults);
        }

        return static::query()->latest('id')->first() ?? static::query()->create($defaults);
    }
}
