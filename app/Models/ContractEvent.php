<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractEvent extends Model
{
    protected $table = 'contract_event';

    protected $primaryKey = 'event_id';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'event_type',
        'contract_id',
        'start_date',
        'end_date',
        'plan_remain_count',
        'plan_limit_date',
        'auto_renewal_flag',
        'additional_info',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'plan_limit_date' => 'date',
        'additional_info' => 'array',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'contract_id');
    }
}
