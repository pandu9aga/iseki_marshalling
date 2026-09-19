<?php

namespace App\Http\Controllers\Perakitan;

use App\Http\Controllers\Controller;
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

    public function search(Request $request)
    {
        $request->validate([
            'sequence_no'     => 'required',
            'production_date' => 'required',
        ]);

        $records = Record::with('member')
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

            $commenterName = null;
            if ($r->Perakitan_Nik) {
                $commenterName = DB::connection('rifa')
                    ->table('employees')
                    ->where('nik', $r->Perakitan_Nik)
                    ->value('nama');
            }

            return [
                'Id_Record'              => $r->Id_Record,
                'Sequence_No'            => $r->Sequence_No_Record,
                'Production_Date'        => $r->Production_Date_Record,
                'Type'                   => $r->Type,
                'Area'                   => $r->Area,
                'Area_Label'             => ucwords(str_replace('_', ' ', $r->Area)),
                'Member_Name'            => $r->member->nama ?? 'Unknown',
                'Member_Nik'             => $r->member->nik ?? '-',
                'Member_Photo'           => $memberPhoto,
                'Time_Record'            => $r->Time_Record ? \Carbon\Carbon::parse($r->Time_Record)->format('d/m/Y H:i') : '-',
                'Perakitan_Comment'      => $r->Perakitan_Comment,
                'Perakitan_Nik'          => $r->Perakitan_Nik,
                'Perakitan_Name'         => $commenterName ?? ($r->Perakitan_Nik ?? '-'),
                'Perakitan_Comment_Time' => $r->Perakitan_Comment_Time ? \Carbon\Carbon::parse($r->Perakitan_Comment_Time)->format('d/m/Y H:i') : null,
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

        $record = Record::findOrFail($id);
        $user = Auth::guard('perakitan')->user();

        $comment = trim($request->input('comment'));

        $record->update([
            'Perakitan_Comment'      => $comment,
            'Perakitan_Nik'          => $user ? $user->nik : null,
            'Perakitan_Comment_Time' => now(),
        ]);

        return response()->json([
            'success'                => true,
            'message'                => 'Komentar berhasil disimpan.',
            'Perakitan_Comment'      => $comment,
            'Perakitan_Nik'          => $user ? $user->nik : null,
            'Perakitan_Name'         => $user ? $user->nama : null,
            'Perakitan_Comment_Time' => now()->format('d/m/Y H:i'),
        ]);
    }
}
