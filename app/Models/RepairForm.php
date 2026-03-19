<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RepairForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_id',
        'warranty_claim_id',
        'form_number',
        'customer_company',
        'contact_person',
        'contact_phone',
        'alternate_contact',
        'alternate_phone',
        'purchase_date',
        'company_phone',
        'email',
        'equipment_name',
        'error_status',
        'includes_adapter',
        'accessories',
        'serial_numbers',
        'warranty_status',
        'employee_count',
        'repair_time_required',
        'estimated_return_date',
        'actual_return_date',
        'estimated_warranty_time',
        'received_by',
        'received_by_phone',
        'service_representative',
        'handed_over_by',
        'handed_over_by_phone',
        'handover_notes',
        'handover_repair_info',
        'handover_check_info',
        'received_date',
        'notes',
        'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'estimated_return_date' => 'date',
        'actual_return_date' => 'date',
        'received_date' => 'date',
        'employee_count' => 'integer',
        'includes_adapter' => 'boolean'
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
    public function scopeDraft($query)
    {
        return $query->where('status', 'not_returned');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'not_returned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'not_returned');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'returned');
    }

    // Accessors
    public function getWarrantyStatusTextAttribute()
    {
        return $this->warranty_status === 'under_warranty' ? 'Còn bảo hành' : 'Hết bảo hành';
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'not_returned' => 'Chưa gửi trả',
            'returned' => 'Đã gửi trả'
        ];
        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'not_returned' => 'warning',
            'returned' => 'success'
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    // Methods
    public static function generateFormNumber()
    {
        $prefix = 'PH' . date('Ymd');
        $lastForm = self::where('form_number', 'like', $prefix . '%')
            ->orderBy('form_number', 'desc')
            ->first();

        if ($lastForm) {
            $lastNumber = intval(substr($lastForm->form_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getSerialNumbersArrayAttribute()
    {
        return explode(',', $this->serial_numbers);
    }

    public function setSerialNumbersArrayAttribute($value)
    {
        $this->attributes['serial_numbers'] = is_array($value) ? implode(',', $value) : $value;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($repairForm) {
            if (empty($repairForm->form_number)) {
                $repairForm->form_number = self::generateFormNumber();
            }
            if (empty($repairForm->received_date)) {
                $repairForm->received_date = now()->toDateString();
            }
        });
    }
}
