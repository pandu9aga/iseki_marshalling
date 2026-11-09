<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainType extends Model
{
    protected $table = 'main_types';
    protected $primaryKey = 'Id_Main_Type';
    public $timestamps = false;

    protected $fillable = [
        'Main_Type',
    ];

    public function types()
    {
        return $this->hasMany(Type::class, 'Id_Main_Type', 'Id_Main_Type');
    }
}
