<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoPart extends Model
{
    protected $table = 'foto_parts';

    protected $fillable = [
        'Id_Marshalling',
        'Sequence_No',
        'Code_Rack',
        'Name_Part',
        'Photo_Path',
    ];

    public function marshalling()
    {
        return $this->belongsTo(Marshalling::class, 'Id_Marshalling', 'Id_Marshalling');
    }
}
