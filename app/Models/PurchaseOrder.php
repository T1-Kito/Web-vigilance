<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'code',
        'order_type',
        'supplier_code',
        'supplier_name',
        'supplier_address',
        'supplier_tax_code',
        'supplier_contact_name',
        'supplier_contact_phone',
        'delivery_date',
        'delivery_location',
        'buyer_name',
        'buyer_position',
        'credit_days',
        'payment_currency',
        'po_number',
        'debt_note',
        'note',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
