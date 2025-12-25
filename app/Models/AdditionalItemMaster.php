<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalItemMaster extends Model
{
    protected $table = 'additional_item_master';

    protected $primaryKey = 'additional_item_type';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'additional_item_type',
        'additional_item',
        'status',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'additional_item' => 'array',
    ];
}
