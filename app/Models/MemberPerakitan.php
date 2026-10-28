<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPerakitan extends Model
{
    protected $table = 'member_perakitans';

    protected $fillable = [
        'nik',
        'area',
        'box',
    ];
}
