<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    protected $table = 'session';

    protected $primaryKey = 'session_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'program_id',
        'location_id',
        'staff_id',
        'start_at',
        'end_at',
        'capacity',
        'exp_capacity',
        'reserved_count',
        'reserved_exp_count',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'additional_info' => 'array',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'session_id', 'session_id');
    }
}
