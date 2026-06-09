<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marshalling extends Model
{
    protected $table = 'marshallings';
    protected $primaryKey = 'Id_Marshalling';
    public $timestamps = false;

    protected $fillable = [
        'Id_Type',
        'Sequence_No',
        'Code_Part',
        'Name_Part',
        'Code_Rack',
        'Difference',
        'Location_Rack',
        'Box',
        'Qty',
        'Mode',
        'Area',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class, 'Id_Type', 'Id_Type');
    }

    public function recordLists()
    {
        return $this->hasMany(Record_List::class, 'Id_Marshalling', 'Id_Marshalling');
    }
}
