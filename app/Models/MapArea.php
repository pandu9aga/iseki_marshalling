<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapArea extends Model
{
    protected $table = 'map_areas';
    protected $fillable = ['Area', 'Location_Rack', 'Sequence_No'];
}
