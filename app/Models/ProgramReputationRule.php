<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramReputationRule extends Model
{
    protected $table = 'program_reputation_rule';

    protected $primaryKey = 'rule_id';

    public $timestamps = false;

    protected $fillable = [
        'program_id',
        'location_id',
        'staff_id',
        'cycle_type',
        'day_of_week',
        'week_of_month',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'capacity',
        'exp_capacity',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
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
}
