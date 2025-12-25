<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'cource_master';

    protected $primaryKey = 'cource_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cource_id',
        'cource_name',
        'cource_category',
        'cource_level',
        'description',
        'status',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
    ];

    public function categorySets(): HasMany
    {
        return $this->hasMany(CourseProgram::class, 'cource_id', 'cource_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class, 'cource_id', 'cource_id');
    }
}
