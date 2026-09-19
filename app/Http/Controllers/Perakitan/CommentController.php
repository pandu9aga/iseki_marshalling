<?php

namespace App\Http\Controllers\Perakitan;

use App\Http\Controllers\Controller;
use App\Models\PartKurang;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index()
    {
        return view('perakitan.comment.index');
    }

    public function myList(Request $request)
    {
        $user = Auth::guard('perakitan')->user();
        if (!$user) {
            return response()->json(['records' => []]);
        }

        $query = PartKurang::with(['member', 'record'])
            ->where('perakitan_nik', $user->nik);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function($q) use ($term) {
                $q->where('sequence_no', 'like', "%{$term}%")
                  ->orWhere('type', 'like', "%{$term}%")
                  ->orWhere('area', 'like', "%{$term}%")
                  ->orWhere('comment', 'like', "%{$term}%");
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
                'Time_Record'            => ($pk->record && $pk->record->Time_Record) ? \Carbon\Carbon::parse($pk->record->Time_Record)->format('d/m/Y H:i') : '-',
                'Perakitan_Comment'      => $pk->comment,
                'Perakitan_Nik'          => $pk->perakitan_nik,
                'Perakitan_Comment_Time' => $pk->comment_time ? $pk->comment_time->format('d/m/Y H:i') : null,
                'Status'                 => $pk->status ?? 'pending',
            ];
        });

        $hasMore = ($page * $perPage) < $total;

        return response()->json([
            'records'      => $records,
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $perPage,
            'has_more'     => $hasMore,
        ]);
    }

    public function markReceived($id)
    {
        $user = Auth::guard('perakitan')->user();
        $partKurang = PartKurang::where('id', $id)
            ->where('perakitan_nik', $user->nik)
            ->firstOrFail();

        $partKurang->update([
            'status'        => 'oke',
            'received_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diubah menjadi Sudah Diterima (Oke).',
            'status'  => 'oke',
        ]);
    }

    public function search(Request $request)
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

            // Ambil part kurang terbaru untuk record ini dari tabel part_kurangs
            $pk = PartKurang::where('id_record', $r->Id_Record)->latest('id')->first();

            $commenterName = null;
            $perakitanNik = $pk ? $pk->perakitan_nik : null;
            if ($perakitanNik) {
                $commenterName = DB::connection('rifa')
                    ->table('employees')
                    ->where('nik', $perakitanNik)
                    ->value('nama');
            }

            return [
                'Id_Record'              => $r->Id_Record,
                'Id_Part_Kurang'         => $pk ? $pk->id : null,
                'Sequence_No'            => $r->Sequence_No_Record,
                'Production_Date'        => $r->Production_Date_Record,
                'Type'                   => $r->Type,
                'Area'                   => $r->Area,
                'Area_Label'             => ucwords(str_replace('_', ' ', $r->Area)),
                'Member_Name'            => $r->member->nama ?? 'Unknown',
                'Member_Nik'             => $r->member->nik ?? '-',
                'Member_Photo'           => $memberPhoto,
                'Time_Record'            => $r->Time_Record ? \Carbon\Carbon::parse($r->Time_Record)->format('d/m/Y H:i') : '-',
                'Perakitan_Comment'      => $pk ? $pk->comment : null,
                'Perakitan_Nik'          => $perakitanNik,
                'Perakitan_Name'         => $commenterName ?? ($perakitanNik ?? '-'),
                'Perakitan_Comment_Time' => ($pk && $pk->comment_time) ? $pk->comment_time->format('d/m/Y H:i') : null,
                'Status'                 => $pk ? $pk->status : 'pending',
            ];
        });

        return response()->json([
            'found'   => true,
            'records' => $results,
        ]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $record = Record::with('member')->findOrFail($id);
        $user = Auth::guard('perakitan')->user();
        $comment = trim($request->input('comment'));

        // Simpan sebagai catatan part kurang baru di tabel part_kurangs (setiap submit menambah row baru)
        $partKurang = PartKurang::create([
            'id_record'       => $record->Id_Record,
            'sequence_no'     => $record->Sequence_No_Record,
            'production_date' => $record->Production_Date_Record,
            'type'            => $record->Type,
            'area'            => $record->Area,
            'id_user'         => $record->Id_User,
            'member_nik'      => $record->member->nik ?? null,
            'perakitan_nik'   => $user ? $user->nik : null,
            'comment'         => $comment,
            'comment_time'    => now(),
            'status'          => 'pending',
            'received_time'   => null,
        ]);

        return response()->json([
            'success'                => true,
            'message'                => 'Catatan part kurang berhasil disimpan ke tabel part_kurangs.',
            'Id_Part_Kurang'         => $partKurang->id,
            'Perakitan_Comment'      => $comment,
            'Perakitan_Nik'          => $user ? $user->nik : null,
            'Perakitan_Name'         => $user ? $user->nama : null,
            'Perakitan_Comment_Time' => now()->format('d/m/Y H:i'),
            'Status'                 => 'pending',
        ]);
    }
}

