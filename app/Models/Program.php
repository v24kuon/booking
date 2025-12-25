<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $table = 'program_master';

    protected $primaryKey = 'program_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'program_id',
        'program_category',
        'program_name',
        'program_level',
        'program_overview',
        'program_detail',
        'program_price',
        'program_point',
        'program_ticket',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'program_price' => 'decimal:0',
        'additional_info' => 'array',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'program_id', 'program_id');
    }
}
