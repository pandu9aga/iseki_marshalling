<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanOrderOverride extends Model
{
    protected $fillable = ['sequence_no', 'mode', 'keterangan'];
}
