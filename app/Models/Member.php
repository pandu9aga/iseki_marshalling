<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    protected $connection = 'rifa';
    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama',
        'nik',
        'password',
        'team',
        'division_id',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}
