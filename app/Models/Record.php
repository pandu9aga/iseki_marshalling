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
        'Time_Record',
        'Remark',
    ];

    public function recordLists()
    {
        return $this->hasMany(Record_List::class, 'Id_Record', 'Id_Record')->orderBy('Sequence_No');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'Id_User', 'id');
    }

    public function partKurangs()
    {
        return $this->hasMany(PartKurang::class, 'id_record', 'Id_Record');
    }

    /**
     * Hitung durasi penyelesaian record dalam detik.
     * Durasi = selisih waktu record_list pertama dan terakhir, dikurangi jam istirahat.
     */
    public function calculateDurationSeconds()
    {
        $recordLists = $this->recordLists->whereNotNull('Time_Record')->sortBy('Sequence_No');
        if ($recordLists->count() < 2) {
            return 0;
        }

        $firstTime = \Carbon\Carbon::parse($recordLists->first()->Time_Record);
        $lastTime = \Carbon\Carbon::parse($recordLists->last()->Time_Record);

        // Pastikan lastTime lebih besar dari firstTime
        if ($firstTime->greaterThanOrEqualTo($lastTime)) {
            return 0;
        }

        // Hitung total detik (gunakan timestamp agar aman di semua versi Carbon)
        $totalSeconds = $lastTime->getTimestamp() - $firstTime->getTimestamp();

        $dayOfWeek = $firstTime->dayOfWeek; // 0=Minggu, 1=Senin, ..., 5=Jumat, 6=Sabtu

        // Definisi jam istirahat
        $breaks = [
            ['start' => '10:00:00', 'end' => '10:10:00'],
            ['start' => '15:00:00', 'end' => '15:10:00'],
        ];

        if ($dayOfWeek == \Carbon\Carbon::FRIDAY) {
            // Jumat: 11.50 - 13.00 (70 menit)
            $breaks[] = ['start' => '11:50:00', 'end' => '13:00:00'];
        } else {
            // Senin-Kamis: 12.00 - 12.40 (40 menit)
            $breaks[] = ['start' => '12:00:00', 'end' => '12:40:00'];
        }

        $totalDeduction = 0;
        $dateStr = $firstTime->format('Y-m-d');

        foreach ($breaks as $break) {
            $breakStart = \Carbon\Carbon::parse($dateStr . ' ' . $break['start']);
            $breakEnd = \Carbon\Carbon::parse($dateStr . ' ' . $break['end']);

            // Hitung overlap antara [firstTime, lastTime] dan [breakStart, breakEnd]
            $overlapStartTs = max($firstTime->getTimestamp(), $breakStart->getTimestamp());
            $overlapEndTs = min($lastTime->getTimestamp(), $breakEnd->getTimestamp());

            if ($overlapStartTs < $overlapEndTs) {
                $totalDeduction += ($overlapEndTs - $overlapStartTs);
            }
        }

        return max(0, $totalSeconds - $totalDeduction);
    }
}

