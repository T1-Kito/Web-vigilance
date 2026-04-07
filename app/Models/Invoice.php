<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'sales_order_id',
        'invoice_code',
        'status',
        'issued_at',
        'vat_percent',
        'discount_percent',
        'sub_total',
        'vat_amount',
        'total_amount',
        'note',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'vat_percent' => 'float',
        'discount_percent' => 'float',
        'sub_total' => 'float',
        'vat_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
