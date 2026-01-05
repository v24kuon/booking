<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_SUSPENDED = 2;

    public const STATUS_CANCELED = 9;

    public const AUTO_RENEWAL_ENABLED = 1;

    public const AUTO_RENEWAL_CANCELED = 9;

    protected $table = 'contract_info';

    protected $primaryKey = 'contract_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'member_id',
        'plan_id',
        'start_date',
        'end_date',
        'plan_remain_count',
        'plan_limit_date',
        'auto_renewal_flag',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_price_id',
        'additional_info',
        'status',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'plan_limit_date' => 'date',
        'additional_info' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ContractEvent::class, 'contract_id', 'contract_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'contract_id', 'contract_id');
    }
}
