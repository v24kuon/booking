<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deadline extends Model
{
    protected $table = 'deadline_master';

    public $timestamps = false;

    /**
     * Single-row config table (no primary key in CSV).
     */
    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = [
        'reserve_deadline',
        'cancel_deadline',
        'withdrowal_deadline',
    ];
}
