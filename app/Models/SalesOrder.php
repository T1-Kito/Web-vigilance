<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $casts = [
        'payment_due_date' => 'date',
        'delivery_due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'source_quote_id',
        'source_order_id',
        'sales_order_code',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'invoice_company_name',
        'invoice_address',
        'customer_tax_code',
        'customer_phone',
        'customer_email',
        'customer_contact_person',
        'staff_code',
        'sales_name',
        'discount_percent',
        'vat_percent',
        'payment_term',
        'payment_due_days',
        'deposit_percent',
        'payment_note',
        'paid_amount',
        'payment_status',
        'payment_due_date',
        'delivery_due_date',
        'paid_at',
        'status',
        'note',
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'source_quote_id');
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }
}
