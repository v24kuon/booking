<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailInfoMaster extends Model
{
    protected $table = 'mail_info_master';

    public $timestamps = false;

    /**
     * Single-row config table (no primary key in CSV).
     */
    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = [
        'mail_sender',
        'verified_mail_title',
        'verified_mail',
        'registered_mail_title',
        'registered_mail',
        'exp_reserved_mail_title',
        'exp_reserved_mail',
        'paid_mail_title',
        'paid_mail',
        'contracted_mail_title',
        'contracted_mail',
        'reserved_mail_title',
        'reserved_mail',
        'reserve_canceled_mail_title',
        'reserve_canceled_mail',
        'withdrawn_mail_title',
        'withdrawn_mail',
    ];
}
