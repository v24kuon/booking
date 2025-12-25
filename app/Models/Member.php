<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory, Notifiable;

    protected $table = 'member_info';

    protected $primaryKey = 'member_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'member_name',
        'member_tel',
        'member_mail',
        'member_birth',
        'member_password',
        'additional_info',
        'status',
    ];

    protected $hidden = [
        'member_password',
    ];

    protected $casts = [
        'crt_time' => 'datetime',
        'upd_time' => 'datetime',
        'member_birth' => 'date',
        'additional_info' => 'array',
        'member_password' => 'hashed',
    ];

    public function getAuthPassword(): string
    {
        return (string) $this->member_password;
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'member_id', 'member_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'member_id', 'member_id');
    }
}
