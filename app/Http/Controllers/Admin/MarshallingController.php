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
            'Area' => 'required|in:sub_assy,sub_engine,transmisi,main_line,mowcol,front_axle',
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
            'Area' => 'required|in:sub_assy,sub_engine,transmisi,main_line,mowcol,front_axle',
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

    public function export()
    {
        $marshallings = Marshalling::with('type')->orderBy('Area')->orderBy('Sequence_No')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Sequence_No', 'Type_Tractor', 'Code_Part', 'Name_Part', 'Code_Rack', 'Difference', 'Location_Rack', 'Box', 'Qty', 'Mode', 'Area'];
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
        $fileName = 'marshallings_' . now()->format('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        try {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            if ($ext === 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            $validModes = ['manual', 'ai'];
            $validAreas = ['sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle'];
            $imported = 0;
            $skipped = 0;
            $totalRows = count($rows) - 1;
            foreach ($rows as $i => $row) {
                if ($i === 0) {
                    continue;
                }

                $sequenceNo = is_numeric(trim($row[0] ?? '')) ? (int) trim($row[0] ?? '') : trim($row[0] ?? '');
                $typeTractor = trim($row[1] ?? '');
                if ($sequenceNo === '' || $typeTractor === '') {
                    $skipped++;
                    continue;
                }

                $type = Type::where('Type', $typeTractor)->first();
                if (!$type) {
                    $skipped++;
                    continue;
                }

                $mode = strtolower(trim($row[9] ?? ''));
                $area = str_replace([' ', '-'], '_', strtolower(trim($row[10] ?? '')));
                if (!in_array($mode, $validModes)) {
                    $skipped++;
                    continue;
                }
                if (!in_array($area, $validAreas)) {
                    $skipped++;
                    continue;
                }

                Marshalling::updateOrCreate(
                    [
                        'Id_Type' => $type->Id_Type,
                        'Area' => $area,
                        'Sequence_No' => $sequenceNo,
                    ],
                    [
                        'Code_Part' => trim($row[2] ?? ''),
                        'Name_Part' => trim($row[3] ?? ''),
                        'Code_Rack' => trim($row[4] ?? ''),
                        'Difference' => trim($row[5] ?? '') ?: '',
                        'Location_Rack' => trim($row[6] ?? ''),
                        'Box' => trim($row[7] ?? ''),
                        'Qty' => trim($row[8] ?? '') !== '' ? trim($row[8] ?? '') : 0,
                        'Mode' => $mode,
                    ]
                );
                $imported++;
            }

            $msg = "$imported of $totalRows marshallings imported.";
            if ($skipped > 0) $msg .= " $skipped rows skipped (invalid type/mode/area or empty).";
            return redirect()->route('admin.types.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->route('admin.types.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function destroy(Marshalling $marshalling)
    {
        $marshalling->delete();
        return response()->json(['message' => 'Marshalling deleted successfully.']);
    }
}
