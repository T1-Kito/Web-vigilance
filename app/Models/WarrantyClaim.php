<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WarrantyStatus;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'warranty_id',
        'claim_number',
        'claim_date',
        'issue_description',
        'customer_complaint',
        'claim_status',
        'admin_notes',
        'repair_notes',
        'estimated_completion_date',
        'actual_completion_date',
        'repair_cost',
        'technician_name'
    ];

    protected $casts = [
        'claim_date' => 'date',
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'repair_cost' => 'decimal:2'
    ];

    // Relationships
    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function statuses()
    {
        return $this->hasMany(WarrantyStatus::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('claim_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('claim_status', 'approved');
    }

    public function scopeInProgress($query)
    {
        return $query->where('claim_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('claim_status', 'completed');
    }

    // Accessors & Mutators
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'in_progress' => 'Đang sửa chữa',
            'completed' => 'Hoàn thành'
        ];
        
        return $statuses[$this->claim_status] ?? $this->claim_status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'in_progress' => 'primary',
            'completed' => 'success'
        ];
        
        return $colors[$this->claim_status] ?? 'secondary';
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->estimated_completion_date || $this->claim_status === 'completed') {
            return false;
        }
        
        return now()->toDateString() > $this->estimated_completion_date;
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        
        return now()->diffInDays($this->estimated_completion_date);
    }

    // Methods
    public function updateStatus($newStatus, $notes = null, $changedBy = null)
    {
        $oldStatus = $this->claim_status;
        $this->update(['claim_status' => $newStatus]);
        
        // Log status change
        $this->statuses()->create([
            'warranty_id' => $this->warranty_id,
            'warranty_claim_id' => $this->id,
            'status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $changedBy ?? 'admin'
        ]);
        
        // If completed, set actual completion date
        if ($newStatus === 'completed' && !$this->actual_completion_date) {
            $this->update(['actual_completion_date' => now()->toDateString()]);
        }
    }

    public function markAsCompleted($repairNotes = null, $technicianName = null)
    {
        $this->update([
            'claim_status' => 'completed',
            'actual_completion_date' => now()->toDateString(),
            'repair_notes' => $repairNotes,
            'technician_name' => $technicianName
        ]);
        
        $this->updateStatus('completed', 'Hoàn thành sửa chữa', $technicianName);
    }
}
