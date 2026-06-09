<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\Marshalling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $member = Auth::guard('member')->user();
        if ($request->ajax()) {
            $data = Record::with('recordLists')
                ->where('Id_User', $member->id);
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $total = $row->recordLists->count();
                    $completed = $row->recordLists->whereNotNull('Time_Record')->count();
                    return "$completed / $total";
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('member.record.record-part', $row->Id_Record) . '" class="btn btn-primary btn-sm"><i class="fas fa-qrcode"></i> Record Part</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('member.records.index');
    }

    public function create()
    {
        $areas = Marshalling::distinct()->orderBy('Area')->pluck('Area');
        return view('member.record.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required',
            'production_date' => 'required',
            'area' => 'required',
        ]);

        $member = Auth::guard('member')->user();

        $sequenceNoFormatted = str_pad($request->sequence_no, 5, '0', STR_PAD_LEFT);

        $plan = DB::connection('podium')
            ->table('plans')
            ->where('Sequence_No_Plan', $sequenceNoFormatted)
            ->where('Production_Date_Plan', $request->production_date)
            ->first();

        if (!$plan) {
            return redirect()->back()->with('error', 'Plan not found in PODIUM system.');
        }

        $typeName = $plan->Type_Plan;

        $record = Record::create([
            'Id_User' => $member->id,
            'Sequence_No_Record' => $sequenceNoFormatted,
            'Production_Date_Record' => $request->production_date,
            'Type' => $typeName,
            'Area' => $request->area,
        ]);

        $marshallings = Marshalling::where('Area', $request->area)
            ->orderBy('Sequence_No')
            ->get();

        if ($marshallings->isEmpty()) {
            $record->delete();
            return redirect()->back()->with('error', 'No marshalling data found for this area.');
        }

        foreach ($marshallings as $m) {
            Record_List::create([
                'Id_Record' => $record->Id_Record,
                'Id_Marshalling' => $m->Id_Marshalling,
                'Sequence_No' => $m->Sequence_No,
                'Code_Part' => $m->Code_Part,
                'Name_Part' => $m->Name_Part,
                'Code_Rack' => $m->Code_Rack,
                'Difference' => $m->Difference,
                'Location_Rack' => $m->Location_Rack,
                'Box' => $m->Box,
                'Qty' => $m->Qty,
                'Mode' => $m->Mode,
                'Area' => $m->Area,
            ]);
        }

        return redirect()->route('member.record.record-part', $record->Id_Record)
            ->with('success', 'Record created. Start recording parts.');
    }

    public function recordPart($id)
    {
        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->findOrFail($id);

        $member = Auth::guard('member')->user();
        if ($record->Id_User != $member->id) {
            abort(403);
        }

        $currentIndex = null;
        foreach ($record->recordLists as $i => $rl) {
            if ($rl->Time_Record === null) {
                $currentIndex = $i;
                break;
            }
        }

        if ($currentIndex === null) {
            return redirect()->route('member.records.index')->with('success', 'All parts recorded!');
        }

        $currentList = $record->recordLists[$currentIndex];
        $prevCompleted = true;
        if ($currentIndex > 0) {
            $prev = $record->recordLists[$currentIndex - 1];
            $prevCompleted = $prev->Time_Record !== null;
        }

        return view('member.record.record-part', compact('record', 'currentList', 'currentIndex', 'prevCompleted'));
    }

    public function updatePart(Request $request, $recordListId)
    {
        $recordList = Record_List::findOrFail($recordListId);
        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->findOrFail($recordList->Id_Record);

        $member = Auth::guard('member')->user();
        if ($record->Id_User != $member->id) {
            abort(403);
        }

        $request->validate([
            'Code_Rack' => 'required',
            'Qty_Record' => 'required|integer|min:0',
        ]);

        if ($request->Code_Rack !== $recordList->Code_Rack) {
            return redirect()->back()->with('error', 'Code Rack does not match! Expected: ' . $recordList->Code_Rack);
        }

        if ($recordList->Mode === 'ai' && (int)$request->Qty_Record !== (int)$recordList->Qty) {
            return redirect()->back()->with('error', 'AI count (' . $request->Qty_Record . ') does not match expected Qty (' . $recordList->Qty . '). Please retake photo.');
        }

        $recordList->update([
            'Qty_Record' => $request->Qty_Record,
            'Time_Record' => now(),
        ]);

        $next = Record_List::where('Id_Record', $record->Id_Record)
            ->whereNull('Time_Record')
            ->orderBy('Sequence_No')
            ->first();

        if ($next) {
            return redirect()->route('member.record.record-part', $record->Id_Record)
                ->with('success', 'Part recorded! Proceed to next part.');
        }

        return redirect()->route('member.records.index')
            ->with('success', 'All parts recorded successfully!');
    }

    public function export(Request $request)
    {
        $member = Auth::guard('member')->user();
        $records = Record::with('recordLists')->where('Id_User', $member->id)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('My Records');

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Sequence No');
        $sheet->setCellValue('C1', 'Production Date');
        $sheet->setCellValue('D1', 'Type');
        $sheet->setCellValue('E1', 'Area');
        $sheet->setCellValue('F1', 'Code Part');
        $sheet->setCellValue('G1', 'Name Part');
        $sheet->setCellValue('H1', 'Code Rack');
        $sheet->setCellValue('I1', 'Qty');
        $sheet->setCellValue('J1', 'Qty Record');
        $sheet->setCellValue('K1', 'Time Record');
        $sheet->setCellValue('L1', 'Status');

        $row = 2;
        $i = 1;
        foreach ($records as $record) {
            foreach ($record->recordLists as $rl) {
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValue('B' . $row, $record->Sequence_No_Record);
                $sheet->setCellValue('C' . $row, $record->Production_Date_Record);
                $sheet->setCellValue('D' . $row, $record->Type);
                $sheet->setCellValue('E' . $row, $record->Area);
                $sheet->setCellValue('F' . $row, $rl->Code_Part);
                $sheet->setCellValue('G' . $row, $rl->Name_Part);
                $sheet->setCellValue('H' . $row, $rl->Code_Rack);
                $sheet->setCellValue('I' . $row, $rl->Qty);
                $sheet->setCellValue('J' . $row, $rl->Qty_Record ?? '-');
                $sheet->setCellValue('K' . $row, $rl->Time_Record ?? '-');
                $sheet->setCellValue('L' . $row, $rl->Time_Record ? 'Done' : 'Pending');
                $row++;
                $i++;
            }
        }

        $lastRow = $row - 1;
        $lastCol = $sheet->getHighestColumn();
        $styleArray = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray($styleArray);
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF4CCCC');
        $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'my_records_' . now()->format('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }
}
