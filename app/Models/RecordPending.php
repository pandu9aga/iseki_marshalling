<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordPending extends Model
{
    protected $table = 'record_pendings';

    protected $fillable = ['Id_Record', 'Id_User', 'Reason', 'Pending_At', 'Resumed_At', 'Taken_By'];

    protected $casts = ['Pending_At' => 'datetime', 'Resumed_At' => 'datetime'];

    public function record()
    {
        return $this->belongsTo(Record::class, 'Id_Record', 'Id_Record');
    }
}
