<?php

namespace App\Http\Controllers;

use App\Models\PartKurang;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicPartKurangController extends Controller
{
    /**
     * Tampilkan halaman utama Part Kurang publik (tanpa login)
     */
    public function index()
    {
        return view('public.part-kurang.index');
    }

    /**
     * Cek dan validasi NIK member dari hasil scan QR
     * Format QR: NIK;Nama;Dept... (split ';' ambil index 0)
     */
    public function checkMember(Request $request)
    {
        $rawQr = trim($request->input('qr', ''));
        if (!$rawQr) {
            return response()->json([
                'valid'   => false,
                'message' => 'NIK atau QR Member wajib diisi.',
            ], 422);
        }

        // Split by ';' dan ambil index 0 sebagai NIK (bisa berupa scan QR ataupun input NIK langsung)
        $parts = explode(';', $rawQr);
        $nik = trim($parts[0]);

        if (!$nik) {
            return response()->json([
                'valid'   => false,
                'message' => 'Format tidak valid. NIK tidak ditemukan.',
            ], 422);
        }

        // Cari data employee di database rifa
        $employee = DB::connection('rifa')
            ->table('employees')
            ->where('nik', $nik)
            ->first(['id', 'nik', 'nama', 'photo_employee', 'team']);

        if (!$employee) {
            return response()->json([
                'valid'   => false,
                'nik'     => $nik,
                'message' => "Member dengan NIK {$nik} tidak terdaftar di sistem.",
            ]);
        }

        $photoBase = '/iseki_rifa/public/photo_employee';
        $photoUrl = $employee->photo_employee ? $photoBase . '/' . $employee->photo_employee : null;

        return response()->json([
            'valid'  => true,
            'member' => [
                'id'    => $employee->id,
                'nik'   => $employee->nik,
                'nama'  => $employee->nama,
                'team'  => $employee->team ?? '-',
                'photo' => $photoUrl,
            ],
        ]);
    }

    /**
     * Cari kanban berdasarkan sequence_no & production_date
     */
    public function searchKanban(Request $request)
    {
        $request->validate([
            'sequence_no'     => 'required',
            'production_date' => 'required',
        ]);

        $records = Record::with(['member', 'partKurangs'])
            ->where('Sequence_No_Record', $request->sequence_no)
            ->where('Production_Date_Record', $request->production_date)
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'found'   => false,
                'message' => 'Member marshalling belum melakukan marshalling nomor instruksi tersebut.',
            ]);
        }

        $photoBase = '/iseki_rifa/public/photo_employee';

        $results = $records->map(function ($r) use ($photoBase) {
            $memberPhoto = null;
            if ($r->member && $r->member->photo_employee) {
                $memberPhoto = $photoBase . '/' . $r->member->photo_employee;
            }

            return [
                'Id_Record'       => $r->Id_Record,
                'Sequence_No'     => $r->Sequence_No_Record,
                'Production_Date' => $r->Production_Date_Record,
                'Type'            => $r->Type,
                'Area'            => $r->Area,
                'Area_Label'      => ucwords(str_replace('_', ' ', $r->Area)),
                'Member_Name'     => $r->member->nama ?? 'Unknown',
                'Member_Nik'      => $r->member->nik ?? '-',
                'Member_Photo'    => $memberPhoto,
                'Time_Record'     => $r->Time_Record ? Carbon::parse($r->Time_Record)->format('d/m/Y H:i') : '-',
            ];
        });

        return response()->json([
            'found'   => true,
            'records' => $results,
        ]);
    }

    /**
     * Simpan catatan part kurang baru ke tabel part_kurangs
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'comment'       => 'required|string|max:1000',
            'perakitan_nik' => 'required|string',
        ]);

        $record = Record::with('member')->findOrFail($id);
        $comment = trim($request->input('comment'));
        $perakitanNik = trim($request->input('perakitan_nik'));

        // Ambil nama pelapor dari rifa jika ada
        $reporterName = DB::connection('rifa')
            ->table('employees')
            ->where('nik', $perakitanNik)
            ->value('nama') ?? $perakitanNik;

        $partKurang = PartKurang::create([
            'id_record'       => $record->Id_Record,
            'sequence_no'     => $record->Sequence_No_Record,
            'production_date' => $record->Production_Date_Record,
            'type'            => $record->Type,
            'area'            => $record->Area,
            'id_user'         => $record->Id_User,
            'member_nik'      => $record->member->nik ?? null,
            'perakitan_nik'   => $perakitanNik,
            'comment'         => $comment,
            'comment_time'    => now(),
            'status'          => 'pending',
            'received_time'   => null,
        ]);

        return response()->json([
            'success'                => true,
            'message'                => 'Catatan part kurang berhasil disimpan.',
            'Id_Part_Kurang'         => $partKurang->id,
            'Perakitan_Comment'      => $comment,
            'Perakitan_Nik'          => $perakitanNik,
            'Perakitan_Name'         => $reporterName,
            'Perakitan_Comment_Time' => now()->format('d/m/Y H:i'),
            'Status'                 => 'pending',
        ]);
    }

    /**
     * Ambil daftar laporan part kurang milik member tertentu (untuk popup modal penerimaan)
     */
    public function memberReports(Request $request)
    {
        $request->validate([
            'nik' => 'required',
        ]);

        $nik = trim($request->nik);
        $status = $request->input('status', 'all');

        $query = PartKurang::with(['member', 'record'])
            ->where('perakitan_nik', $nik);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $photoBase = '/iseki_rifa/public/photo_employee';

        $items = $query->orderBy('comment_time', 'desc')->get();

        $employee = DB::connection('rifa')->table('employees')->where('nik', $nik)->first(['nama', 'photo_employee']);
        $memberName = $employee ? $employee->nama : $nik;
        $memberPhoto = ($employee && $employee->photo_employee) ? $photoBase . '/' . $employee->photo_employee : null;

        $reports = $items->map(function ($pk) use ($photoBase) {
            $marshallingPhoto = null;
            if ($pk->member && $pk->member->photo_employee) {
                $marshallingPhoto = $photoBase . '/' . $pk->member->photo_employee;
            }

            return [
                'Id_Part_Kurang'         => $pk->id,
                'Id_Record'              => $pk->id_record,
                'Sequence_No'            => $pk->sequence_no,
                'Production_Date'        => $pk->production_date,
                'Type'                   => $pk->type,
                'Area'                   => $pk->area,
                'Area_Label'             => ucwords(str_replace('_', ' ', $pk->area)),
                'Member_Name'            => $pk->member->nama ?? 'Unknown',
                'Member_Nik'             => $pk->member_nik ?? ($pk->member->nik ?? '-'),
                'Member_Photo'           => $marshallingPhoto,
                'Time_Record'            => ($pk->record && $pk->record->Time_Record) ? Carbon::parse($pk->record->Time_Record)->format('d/m/Y H:i') : '-',
                'Perakitan_Comment'      => $pk->comment,
                'Perakitan_Nik'          => $pk->perakitan_nik,
                'Perakitan_Comment_Time' => $pk->comment_time ? $pk->comment_time->format('d/m/Y H:i') : null,
                'Status'                 => $pk->status ?? 'pending',
                'Received_Time'          => $pk->received_time ? $pk->received_time->format('d/m/Y H:i') : null,
            ];
        });

        return response()->json([
            'success'       => true,
            'member'        => [
                'nik'   => $nik,
                'nama'  => $memberName,
                'photo' => $memberPhoto,
            ],
            'total'         => $reports->count(),
            'pending_count' => $reports->where('Status', 'pending')->count(),
            'reports'       => $reports,
        ]);
    }

    /**
     * Konfirmasi penerimaan part kurang (ubah pending -> oke)
     */
    public function markReceived(Request $request, $id)
    {
        $partKurang = PartKurang::findOrFail($id);

        $partKurang->update([
            'status'        => 'oke',
            'received_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status part kurang berhasil diubah menjadi Sudah Diterima.',
            'status'  => 'oke',
        ]);
    }

    /**
     * Ambil riwayat part kurang umum (infinite scroll per 5 card) untuk ditampilkan di bagian bawah halaman
     */
    public function recentList(Request $request)
    {
        $query = PartKurang::with(['member', 'record']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function($q) use ($term) {
                $q->where('sequence_no', 'like', "%{$term}%")
                  ->orWhere('type', 'like', "%{$term}%")
                  ->orWhere('area', 'like', "%{$term}%")
                  ->orWhere('comment', 'like', "%{$term}%")
                  ->orWhere('perakitan_nik', 'like', "%{$term}%");
            });
        }

        $photoBase = '/iseki_rifa/public/photo_employee';
        $perPage = 5;
        $page = max(1, (int) $request->input('page', 1));
        $total = $query->count();
        $items = $query->orderBy('comment_time', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $records = $items->map(function ($pk) use ($photoBase) {
            $memberPhoto = null;
            if ($pk->member && $pk->member->photo_employee) {
                $memberPhoto = $photoBase . '/' . $pk->member->photo_employee;
            }

            $reporterName = null;
            if ($pk->perakitan_nik) {
                $reporterName = DB::connection('rifa')
                    ->table('employees')
                    ->where('nik', $pk->perakitan_nik)
                    ->value('nama');
            }

            return [
                'Id_Part_Kurang'         => $pk->id,
                'Id_Record'              => $pk->id_record,
                'Sequence_No'            => $pk->sequence_no,
                'Production_Date'        => $pk->production_date,
                'Type'                   => $pk->type,
                'Area'                   => $pk->area,
                'Area_Label'             => ucwords(str_replace('_', ' ', $pk->area)),
                'Member_Name'            => $pk->member->nama ?? 'Unknown',
                'Member_Nik'             => $pk->member_nik ?? ($pk->member->nik ?? '-'),
                'Member_Photo'           => $memberPhoto,
                'Time_Record'            => ($pk->record && $pk->record->Time_Record) ? Carbon::parse($pk->record->Time_Record)->format('d/m/Y H:i') : '-',
                'Perakitan_Comment'      => $pk->comment,
                'Perakitan_Nik'          => $pk->perakitan_nik,
                'Perakitan_Name'         => $reporterName ?? ($pk->perakitan_nik ?? '-'),
                'Perakitan_Comment_Time' => $pk->comment_time ? $pk->comment_time->format('d/m/Y H:i') : null,
                'Status'                 => $pk->status ?? 'pending',
            ];
        });

        $hasMore = ($page * $perPage) < $total;

        return response()->json([
            'records'  => $records,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'has_more' => $hasMore,
        ]);
    }
}
