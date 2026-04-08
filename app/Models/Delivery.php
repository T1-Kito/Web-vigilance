<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'sales_order_id',
        'delivery_code',
        'status',
        'delivered_at',
        'shipper_name',
        'shipper_phone',
        'receiver_name',
        'receiver_address',
        'delivery_reason',
        'delivery_location',
        'source_document_ref',
        'note',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
