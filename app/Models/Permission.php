<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_group_id',
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    public function group()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }
}
