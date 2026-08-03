<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Punishment extends Model
{
    protected $table = 'punishments';

    protected $fillable = [
        'nik',
    ];
}