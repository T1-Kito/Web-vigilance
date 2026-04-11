<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $casts = [
        'valid_until' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'source_order_id',
        'quote_code',
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
        'payment_term',
        'payment_due_days',
        'deposit_percent',
        'payment_note',
        'note',
        'status',
        'valid_until',
    ];

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceOrder()
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    public function convertedOrder()
    {
        return $this->hasOne(Order::class, 'source_quote_id'); // legacy
    }

    public function convertedSalesOrder()
    {
        return $this->hasOne(SalesOrder::class, 'source_quote_id');
    }
}
