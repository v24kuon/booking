<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdSequence extends Model
{
    protected $table = 'id_sequences';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'next_number',
    ];
}
