<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'types';
    protected $primaryKey = 'Id_Type';
    public $timestamps = false;

    protected $fillable = [
        'Id_Type',
        'Type',
    ];

    public function marshallings()
    {
        return $this->hasMany(Marshalling::class, 'Id_Type', 'Id_Type');
    }
}
