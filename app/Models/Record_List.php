<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record_List extends Model
{
    protected $table = 'record_lists';
    protected $primaryKey = 'Id_Record_List';
    public $timestamps = false;

    protected $fillable = [
        'Id_Record',
        'Id_Marshalling',
        'Code_Part',
        'Name_Part',
        'Code_Rack',
        'Difference',
        'Location_Rack',
        'Box',
        'Qty',
        'Mode',
        'Area',
        'Sequence_No',
        'Qty_Record',
        'Time_Record',
        'Image_Ng',
        'Status_Ng',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class, 'Id_Record', 'Id_Record');
    }

    public function marshalling()
    {
        return $this->belongsTo(Marshalling::class, 'Id_Marshalling', 'Id_Marshalling');
    }
}
