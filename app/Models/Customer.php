<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'tax_id',
        'tax_address',
        'address',
        'invoice_recipient',
        'email',
        'phone',
        'customer_type',
        'company_status',
        'representative',
        'managed_by',
        'active_date',
        'business_type',
        'main_business',
    ];
}
