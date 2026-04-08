<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'sales_order_id',
        'debt_code',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'due_date',
        'last_paid_at',
        'note',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'remaining_amount' => 'float',
        'due_date' => 'date',
        'last_paid_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
