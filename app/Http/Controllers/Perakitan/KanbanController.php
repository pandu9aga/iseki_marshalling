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
        $user = Auth::guard('perakitan')->user();
        $nik = $user ? $user->nik : null;

        $pdfs = collect();
        $asproStorageUrl = '/iseki_aspro/public/storage';

        if ($nik) {
            $now = \Carbon\Carbon::now();
            $cutoff = \Carbon\Carbon::parse('2026-08-01');
            $memberId = null;

            if ($now->lt($cutoff)) {
                $member = \Illuminate\Support\Facades\DB::connection('aspro')
                    ->table('members')
                    ->where('NIK_Member', $nik)
                    ->first();
                if ($member) {
                    $memberId = $member->Id_Member;
                }
            } else {
                $memberId = $user->id;
            }

            if ($memberId) {
                $listReports = \Illuminate\Support\Facades\DB::connection('aspro')
                    ->table('reports as r')
                    ->join('list_reports as lr', 'r.Id_Report', '=', 'lr.Id_Report')
                    ->where('r.Id_Member', $memberId)
                    ->whereYear('r.Start_Report', $now->year)
                    ->whereMonth('r.Start_Report', $now->month)
                    ->select('lr.Name_Procedure', 'lr.Name_Area', 'lr.Name_Tractor')
                    ->distinct()
                    ->get();

                $pdfs = $listReports->map(function ($p) use ($asproStorageUrl) {
                    $relPath = "procedures/{$p->Name_Tractor}/{$p->Name_Area}/{$p->Name_Procedure}.pdf";
                    $physPath = "C:/xampp/htdocs/iseki_aspro/public/storage/{$relPath}";
                    return (object) [
                        'name' => $p->Name_Procedure,
                        'area' => $p->Name_Area,
                        'tractor' => $p->Name_Tractor,
                        'url' => $asproStorageUrl . '/' . $relPath,
                        'exists' => file_exists($physPath),
                    ];
                })->filter(function ($p) {
                    return $p->exists;
                })->values();
            }
        }

        return view('perakitan.kanban.detail', compact('record', 'pdfs'));
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
