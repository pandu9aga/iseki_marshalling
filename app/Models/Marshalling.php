<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marshalling extends Model
{
    protected $table = 'marshallings';
    protected $primaryKey = 'Id_Marshalling';
    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

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
        'No_Instruction',
        'Is_Active',
    ];

    // Sub-area per area induk (urutan = urutan scan); area lain tidak punya sub-area
    const SUB_AREAS = [
        'transmisi' => ['transmisi_a', 'transmisi_b', 'transmisi_c', 'transmisi'],
        'sub_engine' => ['sub_engine_a', 'sub_engine_b', 'sub_engine'],
    ];

    public static function subAreasOf(string $area): array
    {
        return self::SUB_AREAS[$area] ?? [$area];
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'Id_Type', 'Id_Type');
    }

    public function recordLists()
    {
        return $this->hasMany(Record_List::class, 'Id_Marshalling', 'Id_Marshalling');
    }

    // Foto part dari aplikasi iseki_recorder: route /part-photo/{name} redirect ke file foto (name = Code_Rack)
    public function getPhotoUrlAttribute()
    {
        return $this->Code_Rack
            ? 'http://192.168.173.201/iseki_recorder/public/part-photo/' . rawurlencode($this->Code_Rack)
            : null;
    }

    public function fotoPart()
    {
        return $this->hasOne(FotoPart::class, 'Id_Marshalling', 'Id_Marshalling');
    }
}
