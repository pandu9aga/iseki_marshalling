<?php

namespace App\Http\Controllers\Perakitan;

use App\Http\Controllers\Controller;
use App\Models\Record;
use Illuminate\Http\Request;

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
}
