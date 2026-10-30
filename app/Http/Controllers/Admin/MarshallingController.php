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

        $query = Marshalling::query()->where('Is_Active', 1);
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
            'Area' => 'required|in:sub_assy,sub_engine,sub_engine_a,sub_engine_b,transmisi,transmisi_a,transmisi_b,transmisi_c,main_line,mowcol,front_axle,inspeksi,line_a,line_b',
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
            'Area' => 'required|in:sub_assy,sub_engine,sub_engine_a,sub_engine_b,transmisi,transmisi_a,transmisi_b,transmisi_c,main_line,mowcol,front_axle,inspeksi,line_a,line_b',
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

    public function exportZip()
    {
        set_time_limit(0);

        // Ambil semua data marshalling yang aktif dengan relasi type
        $marshallings = Marshalling::with('type')
            ->where('Is_Active', 1)
            ->orderBy('Area')
            ->orderBy('Sequence_No')
            ->get();

        if ($marshallings->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data marshalling untuk diekspor.');
        }

        // Kelompokkan berdasarkan Area lalu Id_Type
        $grouped = $marshallings->groupBy(['Area', 'Id_Type']);

        $tempBaseDir = storage_path('app/temp_export_zip_' . uniqid());
        if (!file_exists($tempBaseDir)) {
            mkdir($tempBaseDir, 0777, true);
        }

        $headers = ['No', 'Type_Tractor', 'Code_Part', 'Name_Part', 'Rack', 'Pembeda', 'Lorong', 'Box', 'Qty', 'Mode (manual/ai)', 'Area (sub_assy/sub_engine/sub_engine_a/sub_engine_b/transmisi/transmisi_a/transmisi_b/transmisi_c/main_line/mowcol/front_axle)'];

        $filesToZip = [];

        foreach ($grouped as $area => $typesGroup) {
            $cleanArea = preg_replace('/[^A-Za-z0-9_\-]/', '_', $area ?: 'unknown_area');
            $areaDir = $tempBaseDir . DIRECTORY_SEPARATOR . $cleanArea;
            if (!file_exists($areaDir)) {
                mkdir($areaDir, 0777, true);
            }

            foreach ($typesGroup as $typeId => $items) {
                $typeName = $items->first()->type ? $items->first()->type->Type : 'Unknown_Type';
                $cleanTypeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $typeName);

                // Nama file excel menyertakan area dan tipe: [Area]_[Tipe].xlsx
                $excelFileName = $cleanArea . '_' . $cleanTypeName . '.xlsx';
                $excelFilePath = $areaDir . DIRECTORY_SEPARATOR . $excelFileName;
                $relativeZipPath = $cleanArea . '/' . $excelFileName;

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                foreach (range('A', 'K') as $i => $col) {
                    $sheet->setCellValue($col . '1', $headers[$i]);
                }

                $row = 2;
                foreach ($items as $m) {
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
                $writer->save($excelFilePath);
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);

                $filesToZip[] = [
                    'full_path' => $excelFilePath,
                    'zip_path' => $relativeZipPath
                ];
            }
        }

        // Buat file ZIP
        $zipFileName = 'marshallings_export_all_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($filesToZip as $file) {
                $zip->addFile($file['full_path'], $file['zip_path']);
            }
            $zip->close();
        }

        // Bersihkan folder temporer
        $this->deleteDirectory($tempBaseDir);

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }

    public function import(Request $request)
    {
        set_time_limit(0); // Unlimited execution time for large imports
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
            
            $allowedAreas = ['front_axle', 'sub_engine', 'transmisi', 'sub_assy', 'main_line', 'inspeksi', 'mowcol'];
            
            // OPTIMIZATION: Preload all MapAreas into memory
            $allMapAreas = \App\Models\MapArea::all();
            $mapAreaDict = [];
            foreach($allMapAreas as $ma) {
                $mapAreaDict[$ma->Area][$ma->Location_Rack] = $ma->Sequence_No;
            }
            
            // OPTIMIZATION: Preload all Types into memory
            $allTypes = \App\Models\Type::all()->pluck('Id_Type', 'Type')->toArray();
            
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetTitle = strtolower(trim($sheet->getTitle()));
                $baseArea = ($sheetTitle === 'mower' || $sheetTitle === 'collector') ? 'mowcol' : $sheetTitle;
                
                if (!in_array($baseArea, $allowedAreas)) continue;
                
                $rows = $sheet->toArray(null, true, true, false);
                if (count($rows) < 9) continue;
                
                // Collect all areas in this sheet
                $areasInSheet = [];
                for ($r = 8; $r < count($rows); $r++) {
                    $subArea = trim($rows[$r][13] ?? '');
                    $area = $baseArea;
                    if ($subArea !== '') {
                        $area .= '_' . strtolower($subArea);
                    }
                    $areasInSheet[$area] = true;
                }
                $sheetAreas = array_keys($areasInSheet);
                
                // Parse headers at row 8 (index 7)
                $headerRow = $rows[7];
                $typeMap = [];
                // Col R is index 17
                for ($c = 17; $c < count($headerRow); $c++) {
                    $typeName = trim($headerRow[$c] ?? '');
                    if ($typeName === '') break; // STOP at first empty column
                    
                    if (!isset($allTypes[$typeName])) {
                        $newType = \App\Models\Type::create(['Type' => $typeName]);
                        $allTypes[$typeName] = $newType->Id_Type;
                    }
                    $typeMap[$c] = $allTypes[$typeName];
                }
                
                // Process PER TYPE (column by column)
                foreach ($typeMap as $colIdx => $typeId) {
                    
                    $insertData = [];
                    $now = now();
                    
                    // PRE-READ PENDING INSTRUCTIONS TO KEEP OLD ACTIVE DATA
                    $pendingSeqs = [];
                    for ($r = 8; $r < count($rows); $r++) {
                        $qtyStr = trim($rows[$r][$colIdx] ?? '');
                        if ($qtyStr === '') continue;
                        
                        $noInstruction = trim($rows[$r][14] ?? '');
                        if ($noInstruction !== '') {
                            $locationRackRaw = trim($rows[$r][11] ?? '');
                            $locationRack = strpos($locationRackRaw, ',') !== false ? trim(explode(',', $locationRackRaw)[0]) : $locationRackRaw;
                            $codeRack = trim($rows[$r][10] ?? '');
                            $subArea = trim($rows[$r][13] ?? '');
                            $area = $baseArea;
                            if ($subArea !== '') {
                                $area .= '_' . strtolower($subArea);
                            }
                            
                            $sequenceNo = 0;
                            if ($locationRack !== '' && isset($mapAreaDict[$area][$locationRack])) {
                                $sequenceNo = $mapAreaDict[$area][$locationRack];
                            } elseif ($codeRack !== '' && isset($mapAreaDict[$area][$codeRack])) {
                                $sequenceNo = $mapAreaDict[$area][$codeRack];
                            }
                            if ($sequenceNo > 0) {
                                $pendingSeqs[] = $sequenceNo;
                            }
                        }
                    }
                    
                    // Fetch old active parts that belong to pending Sequence_Nos
                    $oldActivePartsToKeep = [];
                    if (!empty($pendingSeqs)) {
                        foreach ($sheetAreas as $area) {
                            $oldParts = Marshalling::where('Id_Type', $typeId)
                                ->where('Area', $area)
                                ->whereIn('Sequence_No', array_unique($pendingSeqs))
                                ->where('Is_Active', 1)
                                ->get()
                                ->toArray();
                            $oldActivePartsToKeep = array_merge($oldActivePartsToKeep, $oldParts);
                        }
                    }
                    
                    // DELETE EVERYTHING FOR THIS TYPE & AREA
                    foreach ($sheetAreas as $area) {
                        Marshalling::where('Id_Type', $typeId)->where('Area', $area)->delete();
                    }
                    
                    $excelKeys = [];
                    
                    for ($r = 8; $r < count($rows); $r++) {
                        $row = $rows[$r];
                        
                        $blok = trim($row[2] ?? ''); // C (BLOK)
                        $codePart = trim($row[3] ?? ''); // D (CODE NUMBER)
                        $namePart = trim($row[4] ?? ''); // E (NAME OF PART)
                        $codeRack = trim($row[10] ?? ''); // K (KODE RAK)
                        
                        $locationRackRaw = trim($row[11] ?? ''); // L (NOMER RAK)
                        $locationRack = strpos($locationRackRaw, ',') !== false ? trim(explode(',', $locationRackRaw)[0]) : $locationRackRaw;
                        
                        $skipMark = trim($row[12] ?? ''); // M (PART DI LUAR AREA)
                        $subArea = trim($row[13] ?? ''); // N (KODE LIST)
                        $noInstruction = trim($row[14] ?? ''); // O (NO INTRUKSI)
                        $box = trim($row[15] ?? ''); // P (BOX)
                        $difference = trim($row[16] ?? ''); // Q (PEMBEDA)
                        
                        if ($skipMark !== '' || $codePart === '' || $locationRack === '') {
                            // Only count skipped rows once per sheet
                            if ($colIdx === 17) $skipped++;
                            continue;
                        }
                        
                        $qtyStr = trim($row[$colIdx] ?? '');
                        if ($qtyStr === '') continue;

                        // Ekstrak angka dari $qtyStr (misal '1u' -> 1, '3u' -> 3)
                        $numericQty = (int)preg_replace('/[^0-9]/', '', $qtyStr);
                        if ($numericQty <= 0) continue;
                        
                        // Check if cell is strikethrough (can be slow, but necessary)
                        $isStrikethrough = $sheet->getCell([$colIdx + 1, $r + 1])->getStyle()->getFont()->getStrikethrough();
                        if ($isStrikethrough) continue;
                        
                        $area = $baseArea;
                        if ($subArea !== '') {
                            $area .= '_' . strtolower($subArea);
                        }
                        
                        // OPTIMIZATION: Memory lookup instead of DB query
                        $sequenceNo = 0;
                        if ($locationRack !== '' && isset($mapAreaDict[$area][$locationRack])) {
                            $sequenceNo = $mapAreaDict[$area][$locationRack];
                        } elseif ($codeRack !== '' && isset($mapAreaDict[$area][$codeRack])) {
                            $sequenceNo = $mapAreaDict[$area][$codeRack];
                        }
                        
                        $isActive = ($noInstruction === '') ? 1 : 0;
                        
                        // Parse difference overrides if any
                        $diffOverrides = [];
                        if ($difference !== '') {
                            $diffParts = explode(';', $difference);
                            foreach ($diffParts as $dp) {
                                if (strpos($dp, ':') !== false) {
                                    [$tName, $boxQtys] = explode(':', $dp, 2);
                                    $tName = trim($tName);
                                    
                                    if (!isset($allTypes[$tName])) {
                                        $newT = \App\Models\Type::create(['Type' => $tName]);
                                        $allTypes[$tName] = $newT->Id_Type;
                                    }
                                    $tId = $allTypes[$tName];
                                    $diffOverrides[$tId] = [];
                                    
                                    $bqParts = explode(',', $boxQtys);
                                    foreach ($bqParts as $bq) {
                                        if (strpos($bq, '=') !== false) {
                                            [$bName, $bQty] = explode('=', $bq, 2);
                                            $parsedBQty = (int)preg_replace('/[^0-9]/', '', trim($bQty));
                                            $diffOverrides[$tId][] = ['box' => trim($bName), 'qty' => $parsedBQty];
                                        }
                                    }
                                }
                            }
                        }
                        
                        $entries = [];
                        if (isset($diffOverrides[$typeId]) && count($diffOverrides[$typeId]) > 0) {
                            foreach ($diffOverrides[$typeId] as $ov) {
                                $entries[] = ['box' => $ov['box'], 'qty' => $ov['qty']];
                            }
                        } elseif ($numericQty > 0) {
                            $entries[] = ['box' => $box, 'qty' => $numericQty];
                        }
                        
                        foreach ($entries as $entry) {
                            $mergeKey = $area . '_' . $sequenceNo . '_' . $codePart . '_' . $codeRack . '_' . $entry['box'] . '_' . $noInstruction;
                            
                            if (isset($insertData[$mergeKey])) {
                                $insertData[$mergeKey]['Qty'] += (int)$entry['qty'];
                            } else {
                                $insertData[$mergeKey] = [
                                    'Id_Type' => $typeId,
                                    'Sequence_No' => $sequenceNo,
                                    'Code_Part' => $codePart,
                                    'Name_Part' => $namePart,
                                    'Code_Rack' => $codeRack,
                                    'Difference' => $difference,
                                    'Location_Rack' => $locationRack,
                                    'Box' => $entry['box'],
                                    'Qty' => (int)$entry['qty'],
                                    'Mode' => 'manual',
                                    'Area' => $area,
                                    'No_Instruction' => $noInstruction !== '' ? $noInstruction : null,
                                    'Is_Active' => $isActive,
                                ];
                            }
                            
                            $key = $sequenceNo . '_' . $codePart . '_' . $namePart . '_' . $entry['box'];
                            $excelKeys[$key] = true;
                            $imported++;
                        }
                    }
                    
                    // Re-insert the old active parts that belong to pending instructions
                    // ONLY if they weren't also in the Excel to prevent duplicates
                    foreach ($oldActivePartsToKeep as $oldPart) {
                        $key = $oldPart['Sequence_No'] . '_' . $oldPart['Code_Part'] . '_' . $oldPart['Name_Part'] . '_' . $oldPart['Box'];
                        if (!isset($excelKeys[$key])) {
                            unset($oldPart['Id_Marshalling']);
                            $insertData[] = $oldPart;
                        }
                    }
                    
                    // Bulk insert in chunks
                    foreach (array_chunk($insertData, 500) as $chunk) {
                        Marshalling::insert($chunk);
                    }
                }
            }
            
            $msg = "$imported marshallings imported/updated.";
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
