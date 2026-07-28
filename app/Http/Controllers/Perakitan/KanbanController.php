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

        $pdfProcedures = collect();

        if ($nik) {
            $now = \Carbon\Carbon::now();
            $procedures = \Illuminate\Support\Facades\DB::table('iseki_aspro.reports as r')
                ->join('iseki_rifa.employees as e', 'r.Id_Member', '=', 'e.id')
                ->join('iseki_aspro.list_reports as lr', 'r.Id_Report', '=', 'lr.Id_Report')
                ->where('e.nik', $nik)
                ->whereYear('r.Start_Report', $now->year)
                ->whereMonth('r.Start_Report', $now->month)
                ->select(
                    'lr.Name_Procedure',
                    'lr.Name_Area',
                    'lr.Name_Tractor'
                )
                ->distinct()
                ->get();

            $baseUrl = url('/');
            // aspro storage URL base
            $asproStorageUrl = preg_replace('/\/iseki_marshalling.*/', '/iseki_aspro/public/storage', $baseUrl);
            if ($asproStorageUrl === $baseUrl) {
                $asproStorageUrl = '/iseki_aspro/public/storage';
            }

            $pdfProcedures = $procedures->map(function ($p) use ($asproStorageUrl) {
                $fileRelPath = "procedures/{$p->Name_Tractor}/{$p->Name_Area}/{$p->Name_Procedure}.pdf";
                $fullPhysicalPath = "C:/xampp/htdocs/iseki_aspro/public/storage/" . $fileRelPath;

                return (object) [
                    'name' => $p->Name_Procedure,
                    'area' => $p->Name_Area,
                    'tractor' => $p->Name_Tractor,
                    'url' => $asproStorageUrl . '/' . $fileRelPath,
                    'exists' => file_exists($fullPhysicalPath),
                ];
            });
        }

        return view('perakitan.kanban.detail', compact('record', 'pdfProcedures'));
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
