<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabelSetting extends Model
{
    protected $table = 'label_setting';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'program_label',
        'session_label',
        'staff_label',
        'location_label',
        'reserve_label',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
    ];
}
