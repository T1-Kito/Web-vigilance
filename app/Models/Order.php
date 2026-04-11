<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_code',
        'source_quote_id',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'invoice_company_name',
        'invoice_address',
        'customer_tax_code',
        'customer_phone',
        'customer_email',
        'customer_contact_person',
        'customer_type',
        'staff_code',
        'sales_name',
        'discount_percent',
        'vat_percent',
        'note',
        'payment_method',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
