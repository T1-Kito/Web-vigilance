<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class BorrowRequest extends Model
{
    protected $fillable = [
        'code',
        'requested_by_admin_id',
        'requested_by_name',
        'approved_by_name',
        'department',
        'customer_name',
        'contact_name',
        'tax_code',
        'email',
        'contact_phone',
        'purpose',
        'current_project',
        'borrow_from',
        'borrow_to',
        'deposit_text',
        'deposit_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'borrow_from' => 'date',
        'borrow_to' => 'date',
        'deposit_amount' => 'decimal:2',
    ];

    public function requestedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_admin_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BorrowRequestItem::class, 'borrow_request_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'returned') {
            return false;
        }

        if (!$this->borrow_to) {
            return false;
        }

        return Carbon::today()->greaterThan($this->borrow_to);
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->is_overdue) {
            return 'overdue';
        }

        return (string) ($this->status ?? 'proposed');
    }
}
