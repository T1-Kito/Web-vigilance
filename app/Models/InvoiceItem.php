<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'order_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'line_total',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'int',
        'unit_price' => 'float',
        'line_total' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
