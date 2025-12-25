<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'plan_master';

    protected $primaryKey = 'plan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'plan_type',
        'plan_name',
        'cource_id',
        'plan_usage_count',
        'plan_usage_date',
        'plan_price',
        'additional_info',
        'status',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'plan_price' => 'decimal:0',
        'additional_info' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'cource_id', 'cource_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'plan_id', 'plan_id');
    }
}
