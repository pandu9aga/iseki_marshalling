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
            // Urutan default = urutan scan operator (nextPart); hanya jika user belum klik header kolom,
            // karena Yajra menambahkan order user SETELAH orderBy query ini.
            if (!$request->has('order')) {
                // Mode "box" (default): per box lalu sequence; mode "sequence": urut rak saja
                $byBox = $request->input('sort_mode') !== 'sequence';
                $data->orderBy('Id_Type')
                    ->orderByRaw("FIELD(Area, 'transmisi_a', 'transmisi_b', 'transmisi_c', 'transmisi', 'sub_engine_a', 'sub_engine_b', 'sub_engine')")
                    ->orderBy('Area') // area lain (FIELD = 0) jangan tercampur saat filter "All"
                    ->orderBy($byBox ? 'Box' : 'Sequence_No')
                    ->orderBy($byBox ? 'Sequence_No' : 'Box');
            }
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
        return view('admin.marshallings.index', compact('types', 'areas', 'totalAll', 'selectedArea', 'selectedTypeId', 'selectedType'))->with('scanOrder', \App\Models\MarshallingSetting::scanOrder());
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

    /**
     * Backup seluruh data (aktif & non-aktif) satu type x area ke public/history sebelum dihapus oleh import, urut Sequence_No.
     * Return false jika tidak ada data (tidak ada file dibuat).
     */
    private function backupHistory($typeId, $typeName, $area, $time)
    {
        $rows = Marshalling::where('Id_Type', $typeId)
            ->where('Area', $area)
            ->orderByRaw('Sequence_No = 0') // rak belum ada di map_areas (Sequence_No 0) taruh paling bawah
            ->orderBy('Sequence_No')
            ->orderBy('Id_Marshalling')
            ->get();

        if ($rows->isEmpty()) {
            return false;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Type', 'Sequence_No', 'Code_Part', 'Name_Part', 'Code_Rack', 'Pembeda', 'Location_Rack', 'Box', 'Qty', 'Mode', 'Area', 'No_Instruction', 'Is_Active'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue([$i + 1, 1], $header);
        }

        $r = 2;
        foreach ($rows as $n => $m) {
            $values = [$n + 1, $typeName, $m->Sequence_No, $m->Code_Part, $m->Name_Part, $m->Code_Rack, $m->Difference, $m->Location_Rack, $m->Box, $m->Qty, $m->Mode, $m->Area, $m->No_Instruction, $m->Is_Active];
            // No, Sequence_No, Qty, Is_Active sebagai angka; sisanya teks agar nol di depan Code_Part tidak hilang
            $numericCols = [0, 2, 9, 13];
            foreach ($values as $i => $value) {
                if (in_array($i, $numericCols, true)) {
                    $sheet->setCellValue([$i + 1, $r], (int) $value);
                } else {
                    $sheet->setCellValueExplicit([$i + 1, $r], (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
            $r++;
        }

        // Format sebagai Excel Table (header + filter + baris belang)
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $table = new \PhpOffice\PhpSpreadsheet\Worksheet\Table('A1:' . $lastCol . ($r - 1), 'Marshalling');
        $table->setStyle((new \PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle())
            ->setTheme(\PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle::TABLE_STYLE_MEDIUM2)
            ->setShowRowStripes(true));
        $sheet->addTable($table);
        // Lebar tetap (auto-size mengukur tiap sel dan lambat)
        foreach ($headers as $i => $h) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth(in_array($h, ['Name_Part', 'Code_Part'], true) ? 30 : 14);
        }
        $sheet->freezePane('A2');

        $dir = public_path('history');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $cleanType = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $typeName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($dir . DIRECTORY_SEPARATOR . "{$cleanType}-{$area}-{$time}.xlsx");
        $spreadsheet->disconnectWorksheets();

        return true;
    }

    public function history(Request $request)
    {
        // Area dasar + suffix sub-area opsional dari kolom N import (mis. sub_engine_a)
        $areaList = ['sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle', 'inspeksi', 'line_a', 'line_b'];
        // Pemisah '-' (format baru) atau '_' (file lama)
        $pattern = '/^(.+?)[-_]((?:' . implode('|', $areaList) . ')(?:_[a-z0-9]+)?)[-_](\d{8}_\d{6})\.xlsx$/';

        $rows = [];
        foreach (glob(public_path('history/*.xlsx')) ?: [] as $path) {
            $file = basename($path);
            if (!preg_match($pattern, $file, $mt)) {
                continue;
            }
            $date = \Carbon\Carbon::createFromFormat('Ymd_His', $mt[3]);
            $rows[] = [
                'type' => $mt[1],
                'area_raw' => $mt[2],
                'area' => ucwords(str_replace('_', ' ', $mt[2])),
                'datetime' => $date->format('d-m-Y H:i:s'),
                'ts' => $date->timestamp,
                'file' => $file,
            ];
        }

        // Filter dari halaman marshalling: type_id -> nama type (format sama dengan nama file backup)
        $typeFilter = $request->type;
        $selectedType = $request->filled('type_id') ? Type::find($request->type_id) : null;
        if ($selectedType) {
            $typeFilter = preg_replace('/[^A-Za-z0-9_\-]/', '_', $selectedType->Type);
        }
        $areaFilter = $request->area;

        if ($typeFilter) {
            $rows = array_filter($rows, fn ($r) => $r['type'] === $typeFilter);
        }
        if ($areaFilter) {
            // Area induk (mis. transmisi) ikut menampilkan sub-area (transmisi_a/b/c)
            $rows = array_filter($rows, fn ($r) => $r['area_raw'] === $areaFilter || str_starts_with($r['area_raw'], $areaFilter . '_'));
        }
        usort($rows, fn ($a, $b) => $b['ts'] <=> $a['ts']);

        if ($request->ajax()) {
            return datatables(collect($rows))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . asset('history/' . $row['file']) . '" class="btn btn-success btn-sm" download><i class="fas fa-download"></i> Download</a> ';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-history-btn" data-file="' . e($row['file']) . '"><i class="fas fa-trash"></i> Hapus</button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.marshallings.history', compact('selectedType', 'typeFilter', 'areaFilter'));
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

        // Cegah import ganda berjalan bersamaan (klik berulang) yang saling menghapus/insert data yang sama
        $progressId = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $request->input('progress_id', ''));
        $progressId = substr($progressId, 0, 64);

        $lock = \Illuminate\Support\Facades\Cache::lock('marshalling-import', 600);
        if (!$lock->get()) {
            return $this->importResponse($request, false,
                'Import lain sedang berjalan. Tunggu sampai selesai lalu coba lagi.', 409);
        }

        try {
            $this->setImportProgress($progressId, 2, 'Membaca file Excel...');
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $reader = $ext === 'xls' ? new \PhpOffice\PhpSpreadsheet\Reader\Xls() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            $imported = 0;
            $skipped = 0;
            $noBoxRows = []; // baris Excel (per sheet) yang dilewati karena kolom Box kosong

            $allowedAreas = ['front_axle', 'sub_engine', 'transmisi', 'sub_assy', 'main_line', 'inspeksi', 'mowcol'];

            // OPTIMIZATION: Hanya load sheet area yang dipakai (file asli punya sheet besar lain, mis. 'store produksi')
            // Style tetap di-load karena deteksi strikethrough butuh font.
            $sheetsToLoad = [];
            foreach ($reader->listWorksheetNames($file->getPathname()) as $wsName) {
                $lower = strtolower(trim($wsName));
                $mapped = ($lower === 'mower' || $lower === 'collector') ? 'mowcol' : $lower;
                if (in_array($mapped, $allowedAreas)) {
                    $sheetsToLoad[] = $wsName;
                }
            }
            if (empty($sheetsToLoad)) {
                throw new \Exception('Tidak ada sheet area yang dikenali di file.');
            }
            $reader->setLoadSheetsOnly($sheetsToLoad);
            $this->setImportProgress($progressId, 5, 'Memuat sheet area (' . count($sheetsToLoad) . ' sheet)...');
            $spreadsheet = $reader->load($file->getPathname());
            $totalSheets = count($sheetsToLoad);
            $sheetIndex = 0;

            // OPTIMIZATION: Preload all MapAreas into memory
            $allMapAreas = \App\Models\MapArea::all();
            $mapAreaDict = [];
            foreach($allMapAreas as $ma) {
                $mapAreaDict[$ma->Area][$ma->Location_Rack] = $ma->Sequence_No;
            }

            // OPTIMIZATION: Preload all Types into memory
            $allTypes = \App\Models\Type::all()->pluck('Id_Type', 'Type')->toArray();
            $backupTime = now()->format('Ymd_His');

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetTitle = strtolower(trim($sheet->getTitle()));
                $baseArea = ($sheetTitle === 'mower' || $sheetTitle === 'collector') ? 'mowcol' : $sheetTitle;

                if (!in_array($baseArea, $allowedAreas)) continue;

                $sheetIndex++;
                $sheetStart = 10 + (int) round(85 * ($sheetIndex - 1) / $totalSheets);
                $sheetEnd = 10 + (int) round(85 * $sheetIndex / $totalSheets);
                $this->setImportProgress($progressId, $sheetStart, "Membaca sheet {$baseArea} ({$sheetIndex}/{$totalSheets})");

                // OPTIMIZATION: Jangan hitung ulang rumus (VLOOKUP ke file eksternal sangat lambat);
                // pakai nilai hasil hitung terakhir yang disimpan Excel (oldCalculatedValue).
                $rows = $sheet->toArray(null, false, true, false, false, false, false, true);
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

                $strikeByXf = []; // cache strikethrough per XF index
                $typeTotal = max(count($typeMap), 1);
                $typeIndex = 0;
                // Process PER TYPE (column by column)
                foreach ($typeMap as $colIdx => $typeId) {

                    $typeNameProgress = array_search($typeId, $allTypes);
                    $this->setImportProgress(
                        $progressId,
                        $sheetStart + (int) floor(($sheetEnd - $sheetStart) * $typeIndex / $typeTotal),
                        "Memproses tipe {$typeNameProgress} - {$baseArea} (" . ($typeIndex + 1) . "/{$typeTotal}, sheet {$sheetIndex}/{$totalSheets})"
                    );
                    $typeIndex++;

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
                            // Nilai error Excel (#N/A, #REF!, dst) bukan rak valid; baris ini dilewati saat import
                            if (str_starts_with($codeRack, '#') || str_starts_with($locationRack, '#')) continue;
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
                    $typeName = array_search($typeId, $allTypes);
                    $areasWithoutBackup = [];
                    foreach ($sheetAreas as $area) {
                        // Backup dulu data lama sebelum dihapus
                        if (!$this->backupHistory($typeId, $typeName, $area, $backupTime)) {
                            $areasWithoutBackup[] = $area;
                        }
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

                        // Nilai error Excel (#N/A, #REF!, dst) dari VLOOKUP gagal bukan kode rak valid
                        if ($skipMark !== '' || $codePart === '' || $locationRack === '' || str_starts_with($codeRack, '#') || str_starts_with($locationRack, '#')) {
                            // Only count skipped rows once per sheet
                            if ($colIdx === 17) $skipped++;
                            continue;
                        }

                        $qtyStr = trim($row[$colIdx] ?? '');
//                        \Illuminate\Log\log($colIdx.":". $row[$colIdx]);
                        if ($qtyStr === '') continue;

                        // Ekstrak angka dari $qtyStr (misal '1u' -> 1, '3u' -> 3)
                        $numericQty = (int)preg_replace('/[^0-9]/', '', $qtyStr);
                        if ($numericQty <= 0) continue;

                        // Check if cell is strikethrough (can be slow, but necessary)
                        // Pakai XF index langsung: Cell::getStyle() memanggil setActiveSheetIndex + setSelectedCells (sangat lambat)
                        $xfIndex = $sheet->getCell([$colIdx + 1, $r + 1])->getXfIndex();
                        if (!isset($strikeByXf[$xfIndex])) {
                            $strikeByXf[$xfIndex] = (bool) $spreadsheet->getCellXfByIndex($xfIndex)->getFont()->getStrikethrough();
                        }
                        $isStrikethrough = $strikeByXf[$xfIndex];
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
                            // Part tanpa box tidak bisa diambil operator; jangan di-insert
                            if (trim((string) $entry['box']) === '') {
                                $noBoxRows[$baseArea . '_' . $r] = true;
                                continue;
                            }
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
                        if (!isset($excelKeys[$key]) && trim((string) $oldPart['Box']) !== '') {
                            unset($oldPart['Id_Marshalling']);
                            $insertData[] = $oldPart;
                        }
                    }

                    // Bulk insert in chunks
                    foreach (array_chunk($insertData, 500) as $chunk) {
                        Marshalling::insert($chunk);
                    }

                    // Type x area baru (belum ada data lama): simpan list hasil import sebagai riwayat pertama
                    foreach ($areasWithoutBackup as $area) {
                        $this->backupHistory($typeId, $typeName, $area, $backupTime);
                    }
                }
            }

            $msg = "$imported marshallings imported/updated.";
            if ($skipped > 0) $msg .= " $skipped rows skipped.";
            if ($noBoxRows) $msg .= ' ' . count($noBoxRows) . ' baris tanpa Box tidak diimport.';

            $this->setImportProgress($progressId, 100, $msg, true);

            return $this->importResponse($request, true, $msg);

        } catch (\Exception $e) {
            $errMsg = 'Import failed: ' . $e->getMessage();
            $this->setImportProgress($progressId, 100, $errMsg, true, true);

            return $this->importResponse($request, false, $errMsg, 500);
        } finally {
            $lock->release();
        }
    }

    public function destroyHistory($file)
    {
        // Hanya nama file .xlsx langsung di public/history (cegah path traversal)
        if (!preg_match('/^[A-Za-z0-9_\-]+\.xlsx$/', $file)) {
            return response()->json(['message' => 'Nama file tidak valid.'], 422);
        }

        $path = public_path('history' . DIRECTORY_SEPARATOR . $file);
        if (!is_file($path)) {
            return response()->json(['message' => 'File riwayat tidak ditemukan.'], 404);
        }

        unlink($path);
        return response()->json(['message' => 'Riwayat berhasil dihapus.']);
    }

    public function importProgress($id)
    {
        $id = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $id);
        $data = $id === '' ? null : \Illuminate\Support\Facades\Cache::get("marshalling-import-progress:{$id}");

        return response()->json($data ?: ['percent' => 0, 'message' => 'Menunggu...', 'done' => false, 'error' => false]);
    }

    private function setImportProgress($id, $percent, $message, $done = false, $error = false)
    {
        if ($id === '' || $id === null) return;

        \Illuminate\Support\Facades\Cache::put("marshalling-import-progress:{$id}", [
            'percent' => $percent,
            'message' => $message,
            'done' => $done,
            'error' => $error,
        ], 600);
    }

    private function importResponse(Request $request, bool $ok, string $msg, int $status = 200)
    {
        $request->session()->flash($ok ? 'success' : 'error', $msg);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => $ok, 'message' => $msg], $status);
        }

        return redirect()->route('admin.marshallings.index')->with($ok ? 'success' : 'error', $msg);
    }

    public function destroy(Marshalling $marshalling)
    {
        $marshalling->delete();
        return response()->json(['message' => 'Marshalling deleted successfully.']);
    }
}
