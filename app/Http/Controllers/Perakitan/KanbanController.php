<?php

namespace App\Http\Controllers\Perakitan;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Record_List;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function index()
    {
        return view('perakitan.kanban.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required',
            'production_date' => 'required',
        ]);

        $records = Record::with('member')
            ->where('Sequence_No_Record', $request->sequence_no)
            ->where('Production_Date_Record', $request->production_date)
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'found' => false,
                'message' => 'Member marshalling belum melakukan marshalling nomor instruksi tersebut.',
            ]);
        }

        $results = $records->map(function ($r) {
            return [
                'Id_Record'      => $r->Id_Record,
                'Sequence_No'    => $r->Sequence_No_Record,
                'Production_Date' => $r->Production_Date_Record,
                'Type'           => $r->Type,
                'Area'           => $r->Area,
                'Area_Label'     => ucwords(str_replace('_', ' ', $r->Area)),
                'Member'         => $r->member->nama ?? 'Unknown',
                'Time_Record'    => $r->Time_Record,
            ];
        });

        return response()->json([
            'found'   => true,
            'records' => $results,
        ]);
    }

    public function detail($id)
    {
        $record = Record::with(['recordLists', 'member'])->findOrFail($id);
        return view('perakitan.kanban.detail', compact('record'));
    }

    public function reportEmpty(Request $request, $id)
    {
        $recordList = Record_List::findOrFail($id);
        $user = Auth::guard('perakitan')->user();

        $recordList->update([
            'Report_Empty'  => now(),
            'Reporter_Nik'  => $user->nik,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Part berhasil dilaporkan kosong.',
        ]);
    }
}
