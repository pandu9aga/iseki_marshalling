<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Type::withCount('marshallings');
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('list_marshalling', function ($row) {
                    return $row->marshallings_count;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.marshallings.index', ['type_id' => $row->Id_Type]) . '" class="btn btn-primary btn-sm" title="List Marshalling"><i class="fas fa-list"></i></a> ';
                    $btn .= '<a href="' . route('admin.marshallings.create', ['type_id' => $row->Id_Type]) . '" class="btn btn-info btn-sm" title="Add Marshalling"><i class="fas fa-plus"></i></a> ';
                    $btn .= '<a href="' . route('admin.types.edit', $row->Id_Type) . '" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a> ';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->Id_Type . '"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.types.index');
    }

    public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Type' => 'required|integer|unique:types,Id_Type',
            'Type' => 'required',
        ]);

        Type::create($request->all());
        return redirect()->route('admin.types.index')->with('success', 'Type created successfully.');
    }

    public function edit($id)
    {
        $type = Type::findOrFail($id);
        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = Type::findOrFail($id);
        $request->validate([
            'Id_Type' => 'required|integer|unique:types,Id_Type,' . $id . ',Id_Type',
            'Type' => 'required',
        ]);

        $type->update($request->all());
        return redirect()->route('admin.types.index')->with('success', 'Type updated successfully.');
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

            $imported = 0;
            foreach ($rows as $row) {
                $typeName = trim($row[0] ?? '');
                if ($typeName === '') continue;

                Type::create(['Type' => $typeName]);
                $imported++;
            }

            return redirect()->route('admin.types.index')
                ->with('success', "$imported types imported successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.types.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $types = Type::orderBy('Id_Type')->pluck('Type');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;
        foreach ($types as $type) {
            $sheet->setCellValue('A' . $row, $type);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'types_' . now()->format('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function destroy($id)
    {
        Type::findOrFail($id)->delete();
        return response()->json(['message' => 'Type deleted successfully.']);
    }
}
