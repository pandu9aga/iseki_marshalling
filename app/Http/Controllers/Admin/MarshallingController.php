<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marshalling;
use App\Models\Type;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MarshallingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Marshalling::with('type');
            if ($request->filled('area')) {
                $data->where('Area', $request->area);
            }
            if ($request->filled('type_id')) {
                $data->where('Id_Type', $request->type_id);
            }
            $data->where('Is_Active', 1);
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('type_name', function ($row) {
                    return $row->type ? $row->type->Type : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.marshallings.edit', $row->Id_Marshalling) . '" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a> ';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->Id_Marshalling . '"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->editColumn('Area', function($record) {
                    return $record->Area ? ucwords(str_replace('_', ' ', $record->Area)) : '-';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $types = Type::all();
        $selectedTypeId = $request->type_id;

        $query = Marshalling::query();
        $selectedType = null;
        if ($selectedTypeId) {
            $selectedType = Type::find($selectedTypeId);
            $query->where('Id_Type', $selectedTypeId);
        }
        $areas = (clone $query)->selectRaw('Area, COUNT(*) as total')->groupBy('Area')->orderBy('Area')->get();
        $totalAll = (clone $query)->count();
        $selectedArea = $request->area;
        return view('admin.marshallings.index', compact('types', 'areas', 'totalAll', 'selectedArea', 'selectedTypeId', 'selectedType'));
    }

    public function create(Request $request)
    {
        $types = Type::all();
        $selectedTypeId = $request->type_id;
        return view('admin.marshallings.create', compact('types', 'selectedTypeId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Type' => 'required|exists:types,Id_Type',
            'Sequence_No' => 'required|integer',
            'Code_Part' => 'required',
            'Name_Part' => 'required',
            'Code_Rack' => 'required',
            'Difference' => 'nullable',
            'Location_Rack' => 'required',
            'Box' => 'required',
            'Qty' => 'required|integer',
            'Mode' => 'required|in:manual,ai',
            'Area' => 'required|in:sub_assy,sub_engine,sub_engine_a,sub_engine_b,transmisi,transmisi_a,transmisi_b,transmisi_c,main_line,mowcol,front_axle',
        ]);

        Marshalling::create([
            'Id_Type' => $request->Id_Type,
            'Sequence_No' => $request->Sequence_No,
            'Code_Part' => $request->Code_Part,
            'Name_Part' => $request->Name_Part,
            'Code_Rack' => $request->Code_Rack,
            'Difference' => $request->Difference ?? '',
            'Location_Rack' => $request->Location_Rack,
            'Box' => $request->Box,
            'Qty' => $request->Qty,
            'Mode' => $request->Mode,
            'Area' => $request->Area,
        ]);
        return redirect()->route('admin.marshallings.index')->with('success', 'Marshalling created successfully.');
    }

    public function edit(Marshalling $marshalling)
    {
        $types = Type::all();
        return view('admin.marshallings.edit', compact('marshalling', 'types'));
    }

    public function update(Request $request, Marshalling $marshalling)
    {
        $request->validate([
            'Id_Type' => 'required|exists:types,Id_Type',
            'Sequence_No' => 'required|integer',
            'Code_Part' => 'required',
            'Name_Part' => 'required',
            'Code_Rack' => 'required',
            'Difference' => 'nullable',
            'Location_Rack' => 'required',
            'Box' => 'required',
            'Qty' => 'required|integer',
            'Mode' => 'required|in:manual,ai',
            'Area' => 'required|in:sub_assy,sub_engine,sub_engine_a,sub_engine_b,transmisi,transmisi_a,transmisi_b,transmisi_c,main_line,mowcol,front_axle',
        ]);

        $marshalling->Id_Type = $request->Id_Type;
        $marshalling->Sequence_No = $request->Sequence_No;
        $marshalling->Code_Part = $request->Code_Part;
        $marshalling->Name_Part = $request->Name_Part;
        $marshalling->Code_Rack = $request->Code_Rack;
        $marshalling->Difference = $request->Difference ?? '';
        $marshalling->Location_Rack = $request->Location_Rack;
        $marshalling->Box = $request->Box;
        $marshalling->Qty = $request->Qty;
        $marshalling->Mode = $request->Mode;
        $marshalling->Area = $request->Area;
        $marshalling->save();

        return redirect()->route('admin.marshallings.index')->with('success', 'Marshalling updated successfully.');
    }

    public function export(Request $request)
    {
        $query = Marshalling::with('type');
        if ($request->filled('type_id')) {
            $query->where('Id_Type', $request->type_id);
        }
        if ($request->filled('area')) {
            $query->where('Area', $request->area);
        }
        $marshallings = $query->where('Is_Active', 1)->orderBy('Area')->orderBy('Sequence_No')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Type_Tractor', 'Code_Part', 'Name_Part', 'Rack', 'Pembeda', 'Lorong', 'Box', 'Qty', 'Mode (manual/ai)', 'Area (sub_assy/sub_engine/sub_engine_a/sub_engine_b/transmisi/transmisi_a/transmisi_b/transmisi_c/main_line/mowcol/front_axle)'];
        foreach (range('A', 'K') as $i => $col) {
            $sheet->setCellValue($col . '1', $headers[$i]);
        }

        $row = 2;
        foreach ($marshallings as $m) {
            $sheet->setCellValue('A' . $row, $m->Sequence_No);
            $sheet->setCellValue('B' . $row, $m->type ? $m->type->Type : '');
            $sheet->setCellValue('C' . $row, $m->Code_Part);
            $sheet->setCellValue('D' . $row, $m->Name_Part);
            $sheet->setCellValue('E' . $row, $m->Code_Rack);
            $sheet->setCellValue('F' . $row, $m->Difference);
            $sheet->setCellValue('G' . $row, $m->Location_Rack);
            $sheet->setCellValue('H' . $row, $m->Box);
            $sheet->setCellValue('I' . $row, $m->Qty);
            $sheet->setCellValue('J' . $row, $m->Mode);
            $sheet->setCellValue('K' . $row, $m->Area);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'marshallings_' . date('YmdHis') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $reader = $ext === 'xls' ? new \PhpOffice\PhpSpreadsheet\Reader\Xls() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file->getPathname());
            
            $imported = 0;
            $skipped = 0;
            $newActiveKeys = []; // To track which area & types are processed immediately
            
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetTitle = strtolower(trim($sheet->getTitle()));
                $baseArea = ($sheetTitle === 'mower' || $sheetTitle === 'collector') ? 'mowcol' : $sheetTitle;
                
                $rows = $sheet->toArray(null, true, true, false);
                if (count($rows) < 9) continue;
                
                // Parse headers at row 8 (index 7)
                $headerRow = $rows[7];
                $typeMap = [];
                // Col R is index 17
                for ($c = 17; $c < count($headerRow); $c++) {
                    $typeName = trim($headerRow[$c] ?? '');
                    if ($typeName !== '') {
                        $type = Type::firstOrCreate(['Type' => $typeName]);
                        $typeMap[$c] = $type->Id_Type;
                    }
                }
                
                // Process data rows starting at row 9 (index 8)
                for ($r = 8; $r < count($rows); $r++) {
                    $row = $rows[$r];
                    
                    $codePart = trim($row[2] ?? ''); // C
                    $namePart = trim($row[3] ?? ''); // D
                    $codeRack = trim($row[10] ?? ''); // K
                    $locationRack = trim($row[11] ?? ''); // L
                    $skipMark = trim($row[12] ?? ''); // M
                    $subArea = trim($row[13] ?? ''); // N
                    $noInstruction = trim($row[14] ?? ''); // O
                    $box = trim($row[15] ?? ''); // P
                    $difference = trim($row[16] ?? ''); // Q
                    
                    if ($skipMark !== '' || $codePart === '' || $locationRack === '') {
                        $skipped++;
                        continue;
                    }
                    
                    $area = $baseArea;
                    if ($subArea !== '') {
                        $area .= '_' . strtolower($subArea);
                    }
                    
                    $mapArea = \App\Models\MapArea::where('Area', $area)->where('Location_Rack', $locationRack)->first();
                    $sequenceNo = $mapArea ? $mapArea->Sequence_No : 0;
                    
                    $isActive = ($noInstruction === '') ? 1 : 0;
                    
                    // Parse difference overrides if any
                    $diffOverrides = [];
                    if ($difference !== '') {
                        $diffParts = explode(';', $difference);
                        foreach ($diffParts as $dp) {
                            if (strpos($dp, ':') !== false) {
                                [$tName, $boxQtys] = explode(':', $dp, 2);
                                $tName = trim($tName);
                                $typeObj = Type::firstOrCreate(['Type' => $tName]);
                                $tId = $typeObj->Id_Type;
                                $diffOverrides[$tId] = [];
                                
                                $bqParts = explode(',', $boxQtys);
                                foreach ($bqParts as $bq) {
                                    if (strpos($bq, '=') !== false) {
                                        [$bName, $bQty] = explode('=', $bq, 2);
                                        $diffOverrides[$tId][] = ['box' => trim($bName), 'qty' => (int)trim($bQty)];
                                    }
                                }
                            }
                        }
                    }
                    
                    // Process for each Type
                    foreach ($typeMap as $colIdx => $typeId) {
                        $qtyStr = trim($row[$colIdx] ?? '');
                        
                        $entries = [];
                        if (isset($diffOverrides[$typeId]) && count($diffOverrides[$typeId]) > 0) {
                            foreach ($diffOverrides[$typeId] as $ov) {
                                $entries[] = ['box' => $ov['box'], 'qty' => $ov['qty']];
                            }
                        } elseif (is_numeric($qtyStr) && $qtyStr > 0) {
                            $entries[] = ['box' => $box, 'qty' => (int)$qtyStr];
                        }
                        
                        foreach ($entries as $entry) {
                            Marshalling::updateOrCreate([
                                'Id_Type' => $typeId,
                                'Area' => $area,
                                'Sequence_No' => $sequenceNo,
                                'Code_Part' => $codePart,
                                'Name_Part' => $namePart,
                                'Box' => $entry['box']
                            ], [
                                'Code_Rack' => $codeRack,
                                'Difference' => $difference,
                                'Location_Rack' => $locationRack,
                                'Qty' => $entry['qty'],
                                'Mode' => 'manual',
                                'No_Instruction' => $noInstruction !== '' ? $noInstruction : null,
                                'Is_Active' => $isActive,
                            ]);
                            $imported++;
                            
                            if ($isActive === 1) {
                                $newActiveKeys[$typeId . '|' . $area][] = $sequenceNo;
                            }
                        }
                    }
                }
            }
            
            // Deactivate old records for active imports
            $deactivated = 0;
            foreach ($newActiveKeys as $key => $seqs) {
                [$idType, $area] = explode('|', $key);
                $deactivated += Marshalling::where('Id_Type', $idType)
                    ->where('Area', $area)
                    ->whereNotIn('Sequence_No', $seqs)
                    ->where('Is_Active', 1)
                    ->update(['Is_Active' => 0]);
            }
            
            $msg = "$imported marshallings imported/updated.";
            if ($deactivated > 0) $msg .= " $deactivated marshalling lama di-nonaktifkan.";
            if ($skipped > 0) $msg .= " $skipped rows skipped.";
            
            return redirect()->route('admin.marshallings.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->route('admin.marshallings.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function destroy(Marshalling $marshalling)
    {
        $marshalling->delete();
        return response()->json(['message' => 'Marshalling deleted successfully.']);
    }
}
