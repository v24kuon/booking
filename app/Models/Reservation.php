<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    public const TYPE_NORMAL = 1;

    public const TYPE_TRIAL = 2;

    public const PAYMENT_STATUS_PENDING = 0;

    public const PAYMENT_STATUS_PAID = 1;

    public const PAYMENT_STATUS_CANCELED = 9;

    protected $table = 'reseve_info';

    protected $primaryKey = 'reserve_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'reserve_id',
        'member_id',
        'session_id',
        'program_id',
        'trial_program_id',
        'contract_id',
        'reserve_payment',
        'reserve_type',
        'channel',
        'reserve_status',
        'payment_status',
        'attendance_status',
        'canceled_at',
        'cancel_reason',
        'additional_info',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'canceled_at' => 'datetime',
        'additional_info' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id', 'session_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'contract_id');
    }

    public function isTrialType(): bool
    {
        return (int) $this->reserve_type === self::TYPE_TRIAL;
    }

    public function getTypeLabel(): string
    {
        return $this->isTrialType() ? '体験' : '通常';
    }
}
