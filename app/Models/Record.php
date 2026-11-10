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
        'Scan_Order',
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

    public function pendings()
    {
        return $this->hasMany(RecordPending::class, 'Id_Record', 'Id_Record');
    }

    /**
     * Ringkasan siapa yang men-scan part record ini (Assigned_To NULL = pemilik). Nama diresolve oleh pemanggil.
     */
    public function scannersSummary()
    {
        return $this->recordLists->whereNotNull('Time_Record')
            ->groupBy(fn ($l) => $l->Assigned_To ?? $this->Id_User)
            ->map(fn ($g, $id) => [
                'id' => (int) $id,
                'role' => $id == $this->Id_User ? 'Pemilik' : 'Helper',
                'parts' => $g->count(),
                'first' => $g->min('Time_Record'),
                'last' => $g->max('Time_Record'),
            ])->sortBy(fn ($r) => $r['role'] === 'Pemilik' ? 0 : 1)->values();
    }

    /**
     * Teks helper yang membantu, mis. "Budi (25 part)"; kosong jika tidak ada. $names = [id => nama] dari rifa.
     */
    public function helpersLabel($names)
    {
        return $this->scannersSummary()->where('role', 'Helper')
            ->map(fn ($h) => ($names[$h['id']] ?? '-') . " ({$h['parts']} part)")->implode(', ');
    }
    /**
     * Hitung durasi penyelesaian record dalam detik.
     * Durasi = selisih waktu record_list pertama dan terakhir, dikurangi jam istirahat.
     */
    public function calculateDurationSeconds()
    {
        // Urutan scan bisa berbeda dari Sequence_No, jadi ambil waktu paling awal & akhir
        $times = $this->recordLists->whereNotNull('Time_Record')
            ->map(fn ($rl) => \Carbon\Carbon::parse($rl->Time_Record));
        if ($times->count() < 2) {
            return 0;
        }

        $firstTime = $times->min();
        $lastTime = $times->max();

        // Pastikan lastTime lebih besar dari firstTime
        if ($firstTime->greaterThanOrEqualTo($lastTime)) {
            return 0;
        }

        // Hitung total detik (gunakan timestamp agar aman di semua versi Carbon)
        $totalSeconds = $lastTime->getTimestamp() - $firstTime->getTimestamp();

        // Kurangi waktu pending (gabungan interval yang tumpang tindih), dipotong ke [firstTime, lastTime].
        // shortcut: pending yang melewati jam istirahat bisa terkurang dua kali; dan jika helper tetap scan saat pemilik pending,
        // waktu itu ikut terpotong (perkiraan). Upgrade: hitung durasi per member.
        $first = $firstTime->getTimestamp();
        $last = $lastTime->getTimestamp();
        $pendingSeconds = 0;
        $cursor = $first;
        $intervals = $this->pendings->map(fn ($p) => [
            max($first, $p->Pending_At->getTimestamp()),
            min($last, $p->Resumed_At ? $p->Resumed_At->getTimestamp() : $last),
        ])->sortBy(0);
        foreach ($intervals as [$s, $e]) {
            $s = max($s, $cursor);
            if ($s < $e) {
                $pendingSeconds += $e - $s;
                $cursor = $e;
            }
        }
        $totalSeconds -= $pendingSeconds;
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

