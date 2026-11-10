<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'types';
    protected $primaryKey = 'Id_Type';
    public $timestamps = false;

    protected $fillable = [
        'Id_Type',
        'Type',
        'Id_Main_Type',
    ];

    public function mainType()
    {
        return $this->belongsTo(MainType::class, 'Id_Main_Type', 'Id_Main_Type');
    }

    public function marshallings()
    {
        return $this->hasMany(Marshalling::class, 'Id_Type', 'Id_Type');
    }

    /**
     * Resolusi mapping tipe kanban produksi ke tipe master marshalling.
     * Khusus SXG 3:
     * - SXG 324 K (mengandung K / KR) -> SXG324KRE42S
     * - SXG 324 G (mengandung G, bukan K) -> SXG324GE42S
     * - SXG 324 Non-G & Non-K -> SXG324E42S
     * - SXG 327 K (mengandung K / KR) -> SXG327KRE42S
     * - SXG 327 G (mengandung G, bukan K) -> SXG327GE42S
     * - SXG 327 Non-G & Non-K -> SXG327E42S
     */
    public static function resolveTypeName(string $rawType): string
    {
        $clean = strtoupper(trim($rawType));

        // Khusus SXG 3: diawali SXG324 atau SXG327
        // Catatan: 'SXG' sendiri mengandung huruf 'G', jadi pengecekan varian (K, G, Non-K/Non-G)
        // harus dilakukan pada suffix setelah model prefix 'SXG324' / 'SXG327'.
        if (preg_match('/^SXG324(.*)$/i', $clean, $matches)) {
            $suffix = $matches[1] ?? '';
            if (strpos($suffix, 'K') !== false) {
                return 'SXG324KRE42S';
            }
            if (strpos($suffix, 'G') !== false) {
                return 'SXG324GE42S';
            }
            return 'SXG324E42S';
        }

        if (preg_match('/^SXG327(.*)$/i', $clean, $matches)) {
            $suffix = $matches[1] ?? '';
            if (strpos($suffix, 'K') !== false) {
                return 'SXG327KRE42S';
            }
            if (strpos($suffix, 'G') !== false) {
                return 'SXG327GE42S';
            }
            return 'SXG327E42S';
        }

        return $clean;
    }

    /**
     * Cari Model Type berdasarkan nama tipe mentah dari kanban.
     */
    public static function findByRawType(?string $rawType): ?self
    {
        if (!$rawType) {
            return null;
        }

        $clean = strtoupper(trim($rawType));
        $mapped = self::resolveTypeName($clean);

        // Cari berdasarkan mapped type dulu
        $type = self::where('Type', $mapped)->first();
        if ($type) {
            return $type;
        }

        // Fallback cari persis teks aslinya
        if ($mapped !== $clean) {
            return self::where('Type', $clean)->first();
        }

        return null;
    }
}
