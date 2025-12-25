<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $table = 'location_master';

    protected $primaryKey = 'location_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'location_id',
        'location_name',
        'location_address',
        'location_tel',
        'location_mail',
        'description',
        'status',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(LocationImage::class, 'location_id', 'location_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'location_id', 'location_id');
    }
}
