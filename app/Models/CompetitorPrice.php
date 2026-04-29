<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitorPrice extends Model
{
    protected $fillable = [
        'competitor_name',
        'product_key',
        'product_name_raw',
        'price',
        'product_url',
        'checked_at',
    ];

    protected $casts = [
        'price' => 'float',
        'checked_at' => 'datetime',
    ];
}
