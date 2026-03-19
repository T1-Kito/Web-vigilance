<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\WarrantyClaim;
use App\Models\WarrantyStatus;

class Warranty extends Model
{
    protected $fillable = [
        'serial_number',
        'product_id',
        'model_name',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'purchase_date',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_period_months',
        'invoice_number',
        'notes',
        'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function claims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function statuses()
    {
        return $this->hasMany(WarrantyStatus::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeValid($query)
    {
        return $query->where('warranty_end_date', '>=', now()->toDateString());
    }

    // Accessors & Mutators
    public function getIsExpiredAttribute()
    {
        return $this->warranty_end_date < now()->toDateString();
    }

    public function getDaysRemainingAttribute()
    {
        return (int)now()->diffInDays($this->warranty_end_date, false);
    }

    public function getExpiredTimeTextAttribute()
    {
        if ($this->is_expired) {
            $endDate = Carbon::parse($this->warranty_end_date);
            $days = (int)$endDate->diffInDays(now());
            
            if ($days >= 365) {
                $years = (int)($days / 365);
                return "Hết hạn {$years} " . ($years == 1 ? 'năm' : 'năm') . " trước";
            } elseif ($days >= 30) {
                $months = (int)($days / 30);
                return "Hết hạn {$months} " . ($months == 1 ? 'tháng' : 'tháng') . " trước";
            } else {
                return "Hết hạn {$days} " . ($days == 1 ? 'ngày' : 'ngày') . " trước";
            }
        }
        
        return null;
    }

    public function getRemainingTimeTextAttribute()
    {
        if ($this->is_expired) {
            return null;
        }
        
        $endDate = Carbon::parse($this->warranty_end_date);
        $days = (int)now()->diffInDays($endDate, false);
        
        if ($days <= 0) {
            return "Hết hạn hôm nay";
        }
        
        if ($days >= 365) {
            $years = (int)($days / 365);
            return "Còn {$years} " . ($years == 1 ? 'năm' : 'năm');
        } elseif ($days >= 30) {
            $months = (int)($days / 30);
            return "Còn {$months} " . ($months == 1 ? 'tháng' : 'tháng');
        } else {
            return "Còn {$days} " . ($days == 1 ? 'ngày' : 'ngày');
        }
    }

    public function getWarrantyStatusTextAttribute()
    {
        if ($this->status === 'cancelled') {
            return 'Đã hủy';
        }
        
        if ($this->is_expired) {
            return 'Hết hạn bảo hành';
        }
        
        return 'Còn hiệu lực';
    }

    public function getWarrantyStatusColorAttribute()
    {
        if ($this->status === 'cancelled') {
            return 'danger';
        }
        
        if ($this->is_expired) {
            return 'warning';
        }
        
        return 'success';
    }

    // Methods
    public function updateStatus()
    {
        if ($this->status === 'cancelled') {
            return;
        }

        if ($this->is_expired && $this->status === 'active') {
            $this->update(['status' => 'expired']);
            
            // Log status change
            $this->statuses()->create([
                'status' => 'expired',
                'notes' => 'Bảo hành tự động hết hạn',
                'changed_by' => 'system'
            ]);
        }
    }

    public function canClaimWarranty()
    {
        return $this->status === 'active' && !$this->is_expired;
    }

    public function getLatestClaim()
    {
        return $this->claims()->latest()->first();
    }
}
