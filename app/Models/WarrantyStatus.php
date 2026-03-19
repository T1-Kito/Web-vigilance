<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyStatus extends Model
{
    protected $fillable = [
        'warranty_id',
        'warranty_claim_id',
        'status',
        'notes',
        'changed_by'
    ];

    // Relationships
    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function warrantyClaim()
    {
        return $this->belongsTo(WarrantyClaim::class);
    }

    // Scopes
    public function scopeForWarranty($query, $warrantyId)
    {
        return $query->where('warranty_id', $warrantyId);
    }

    public function scopeForClaim($query, $claimId)
    {
        return $query->where('warranty_claim_id', $claimId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
