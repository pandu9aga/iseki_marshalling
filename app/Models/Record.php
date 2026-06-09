<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'records';
    protected $primaryKey = 'Id_Record';
    public $timestamps = false;

    protected $fillable = [
        'Id_User',
        'Sequence_No_Record',
        'Production_Date_Record',
        'Type',
        'Area',
    ];

    public function recordLists()
    {
        return $this->hasMany(Record_List::class, 'Id_Record', 'Id_Record')->orderBy('Sequence_No');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'Id_User', 'id');
    }
}
