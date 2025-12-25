<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff_master';

    protected $primaryKey = 'staff_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'staff_name',
        'staff_gender',
        'staff_age',
        'licence_skill',
        'main_expertise',
        'staff_role',
        'description',
        'additional_info',
        'status',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'additional_info' => 'array',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(StaffImage::class, 'staff_id', 'staff_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'staff_id', 'staff_id');
    }
}
