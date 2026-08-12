<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberArea extends Model
{
    protected $table = 'member_areas';

    protected $fillable = [
        'nik',
        'area',
    ];
}