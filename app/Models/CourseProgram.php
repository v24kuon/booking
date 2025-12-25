<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseProgram extends Model
{
    protected $table = 'course_program';

    public $timestamps = false;

    /**
     * Composite key table (cource_id + program_category).
     * Eloquent doesn't support composite primary keys, so treat as keyless model.
     */
    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = [
        'cource_id',
        'program_category',
        'crt_time',
        'upd_time',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'cource_id', 'cource_id');
    }
}
