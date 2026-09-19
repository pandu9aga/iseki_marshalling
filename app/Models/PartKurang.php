<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartKurang extends Model
{
    protected $table = 'part_kurangs';

    protected $fillable = [
        'id_record',
        'sequence_no',
        'production_date',
        'type',
        'area',
        'id_user',
        'member_nik',
        'perakitan_nik',
        'comment',
        'comment_time',
        'status',
        'received_time',
    ];

    protected $casts = [
        'comment_time'  => 'datetime',
        'received_time' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class, 'id_record', 'Id_Record');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'id_user', 'id');
    }
}
