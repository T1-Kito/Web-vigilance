<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'user_id',
        'source_quote_id',
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
}
