<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarshallingSetting extends Model
{
    protected $fillable = ['key', 'value'];

    // Urutan scan default (box|sequence) untuk record baru
    public static function scanOrder(): string
    {
        return static::where('key', 'scan_order')->value('value') ?: 'box';
    }

    // Pengecualian per No. Sequence produksi menang atas default global
    public static function scanOrderFor(?string $sequenceNo): string
    {
        $override = $sequenceNo === null ? null
            : ScanOrderOverride::where('sequence_no', trim($sequenceNo))->value('mode');

        return $override ?: static::scanOrder();
    }
}
