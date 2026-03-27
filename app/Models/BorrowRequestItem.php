<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowRequestItem extends Model
{
    protected $fillable = [
        'borrow_request_id',
        'line_no',
        'item_name',
        'unit',
        'quantity',
        'value',
        'note',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id');
    }
}
