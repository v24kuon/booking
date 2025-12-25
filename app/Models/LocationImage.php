<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationImage extends Model
{
    protected $table = 'location_img';

    protected $primaryKey = 'img_id';

    public $timestamps = false;

    protected $fillable = [
        'location_id',
        'img_path',
        'img_type',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
}
