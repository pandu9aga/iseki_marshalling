<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Record_List;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Record::with(['recordLists', 'member']);
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    return $row->member ? $row->member->nama : '-';
                })
                ->addColumn('status', function ($row) {
                    $total = $row->recordLists->count();
                    $completed = $row->recordLists->whereNotNull('Time_Record')->count();
                    return "$completed / $total";
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-info btn-sm view-btn" data-id="' . $row->Id_Record . '"><i class="fas fa-eye"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.records.index');
    }

    public function show($id)
    {
        $record = Record::with(['recordLists', 'member'])->findOrFail($id);
        return response()->json($record);
    }

    public function approveNg($recordListId)
    {
        $recordList = Record_List::findOrFail($recordListId);
        $recordList->update([
            'Status_Ng' => 'ng_ok',
        ]);

        return response()->json(['success' => true, 'status' => 'ng_ok']);
    }

    public function export(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $records = Record::with(['recordLists', 'member']);
        if ($start_date && $end_date) {
            $records->whereBetween('Id_Record', [$start_date, $end_date]);
        }
        $records = $records->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Records');

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Sequence No');
        $sheet->setCellValue('C1', 'Production Date');
        $sheet->setCellValue('D1', 'Type');
        $sheet->setCellValue('E1', 'Area');
        $sheet->setCellValue('F1', 'Member');
        $sheet->setCellValue('G1', 'Code Part');
        $sheet->setCellValue('H1', 'Name Part');
        $sheet->setCellValue('I1', 'Code Rack');
        $sheet->setCellValue('J1', 'Qty');
        $sheet->setCellValue('K1', 'Qty Record');
        $sheet->setCellValue('L1', 'Time Record');
        $sheet->setCellValue('M1', 'Status');

        $row = 2;
        $i = 1;
        foreach ($records as $record) {
            $memberName = $record->member ? $record->member->nama : '-';
            foreach ($record->recordLists as $rl) {
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValue('B' . $row, $record->Sequence_No_Record);
                $sheet->setCellValue('C' . $row, $record->Production_Date_Record);
                $sheet->setCellValue('D' . $row, $record->Type);
                $sheet->setCellValue('E' . $row, $record->Area);
                $sheet->setCellValue('F' . $row, $memberName);
                $sheet->setCellValue('G' . $row, $rl->Code_Part);
                $sheet->setCellValue('H' . $row, $rl->Name_Part);
                $sheet->setCellValue('I' . $row, $rl->Code_Rack);
                $sheet->setCellValue('J' . $row, $rl->Qty);
                $sheet->setCellValue('K' . $row, $rl->Qty_Record ?? '-');
                $sheet->setCellValue('L' . $row, $rl->Time_Record ?? '-');
                $sheet->setCellValue('M' . $row, $rl->Time_Record ? 'Done' : 'Pending');
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
        $fileName = 'records_' . now()->format('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }
}
