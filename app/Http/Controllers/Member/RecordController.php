<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\Marshalling;
use App\Models\Type;
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
                ->editColumn('Area', function($record) {
                    return $record->Area ? ucwords(str_replace('_', ' ', $record->Area)) : '-';
                })
                ->setRowId(function ($row) {
                    return $row->Id_Record;
                })
                ->make(true);
        }
        return view('member.records.index');
    }

    public function create()
    {
        return view('member.record.create');
    }

    public function getAreasByType(Request $request)
    {
        $typeName = $request->query('type');
        if (!$typeName) {
            return response()->json([]);
        }

        $type = Type::where('Type', $typeName)->first();
        if (!$type) {
            return response()->json([]);
        }

        $areas = Marshalling::where('Id_Type', $type->Id_Type)
            ->distinct()
            ->orderBy('Area')
            ->pluck('Area');

        return response()->json($areas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required',
            'production_date' => 'required',
            'type' => 'required',
            'area' => 'required',
        ]);

        $member = Auth::guard('member')->user();

        $record = Record::create([
            'Id_User' => $member->id,
            'Sequence_No_Record' => $request->sequence_no,
            'Production_Date_Record' => $request->production_date,
            'Type' => $request->type,
            'Area' => $request->area,
            'Time_Record' => now(),
        ]);

        $type = Type::where('Type', trim($request->type))->first();

        if (!$type) {
            $record->delete();
            return redirect()->back()->with('error', 'Type "' . $request->type . '" tidak ditemukan di master data.');
        }

        $marshallings = Marshalling::where('Area', $request->area)
            ->where('Id_Type', $type->Id_Type)
            ->orderBy('Sequence_No')
            ->get();

        if ($marshallings->isEmpty()) {
            $record->delete();
            return redirect()->back()->with('error', 'No marshalling data found for this area and type.');
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

        return view('member.record.record-part', compact('record', 'currentIndex'));
    }

    public function scanPart($recordId, $recordListId)
    {
        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->findOrFail($recordId);

        $member = Auth::guard('member')->user();
        if ($record->Id_User != $member->id) {
            abort(403);
        }

        $recordList = $record->recordLists->firstWhere('Id_Record_List', $recordListId);
        if (!$recordList) {
            abort(404);
        }

        if ($recordList->Time_Record !== null) {
            return redirect()->route('member.record.record-part', $record->Id_Record)
                ->with('error', 'This part has already been recorded.');
        }

        $prevCompleted = true;
        $prev = $record->recordLists->firstWhere('Sequence_No', $recordList->Sequence_No - 1);
        if ($prev) {
            $prevCompleted = $prev->Time_Record !== null;
        }

        return view('member.record.record-scan', compact('record', 'recordList', 'prevCompleted'));
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

        $updateData = [
            'Qty_Record' => $request->Qty_Record,
            'Time_Record' => now(),
        ];

        if ($recordList->Mode === 'ai' && $request->filled('image_data')) {
            $qtyMatch = (int)$request->Qty_Record === (int)$recordList->Qty;
            if (!$qtyMatch) {
                $imageData = $request->input('image_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                    $base64 = substr($imageData, strpos($imageData, ',') + 1);
                    $decoded = base64_decode($base64);
                    if ($decoded === false) {
                        $decoded = null;
                    }
                }

                if (!empty($decoded)) {
                    $folder = 'uploads/ng/' . now()->format('mY');
                    $publicPath = public_path($folder);
                    if (!is_dir($publicPath)) {
                        mkdir($publicPath, 0755, true);
                    }

                    $filename = 'ng_' . $recordList->Id_Record_List . '_' . now()->format('YmdHis') . '.' . $ext;
                    $filepath = $publicPath . '/' . $filename;

                    $img = imagecreatefromstring($decoded);
                    if ($img) {
                        $quality = 90;
                        ob_start();
                        imagejpeg($img, null, $quality);
                        ob_end_clean();

                        $bytes = strlen($decoded);
                        if ($bytes > 500000) {
                            $quality = (int)(90 * (500000 / $bytes));
                            $quality = max(10, min(90, $quality));
                        }

                        if ($ext === 'jpg' || $ext === 'jpeg') {
                            imagejpeg($img, $filepath, $quality);
                        } elseif ($ext === 'png') {
                            $pngQuality = (int)(9 - ($quality / 10));
                            imagepng($img, $filepath, max(0, min(9, $pngQuality)));
                        } else {
                            imagejpeg($img, $filepath, $quality);
                        }
                        imagedestroy($img);

                        $filesize = filesize($filepath);
                        if ($filesize > 500000 && file_exists($filepath)) {
                            $jpgData = file_get_contents($filepath);
                            $img2 = imagecreatefromstring($jpgData);
                            if ($img2) {
                                $quality = (int)(90 * (500000 / $filesize));
                                $quality = max(5, min(85, $quality));
                                imagejpeg($img2, $filepath, $quality);
                                imagedestroy($img2);
                            }
                        }

                        $updateData['Image_Ng'] = $folder . '/' . $filename;
                    }
                }
            }
        }

        $recordList->update($updateData);

        $next = Record_List::where('Id_Record', $record->Id_Record)
            ->whereNull('Time_Record')
            ->orderBy('Sequence_No')
            ->first();

        if ($next) {
            return redirect()->route('member.record.record-part', $record->Id_Record)
                ->with('success', 'Part recorded! Proceed to next part.');
        }

        return redirect()->route('member.record.record-part', $record->Id_Record)
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
