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
        'Id_Main_Type',
    ];

    public function mainType()
    {
        return $this->belongsTo(MainType::class, 'Id_Main_Type', 'Id_Main_Type');
    }

    public function marshallings()
    {
        return $this->hasMany(Marshalling::class, 'Id_Type', 'Id_Type');
    }
}
