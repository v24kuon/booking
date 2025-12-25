<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffImage extends Model
{
    protected $table = 'staff_img';

    protected $primaryKey = 'img_id';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'img_path',
        'img_type',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
