<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = \App\Models\MainType::withCount('types');
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.main-types.edit', $row->Id_Main_Type) . '" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a> ';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->Id_Main_Type . '"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.main_types.index');
    }

    public function create()
    {
        return view('admin.main_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Main_Type' => 'required',
        ]);

        \App\Models\MainType::create($request->all());

        return redirect()->route('admin.main-types.index')->with('success', 'Main Type created successfully.');
    }

    public function edit($id)
    {
        $mainType = \App\Models\MainType::findOrFail($id);
        return view('admin.main_types.edit', compact('mainType'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Main_Type' => 'required',
        ]);

        $mainType = \App\Models\MainType::findOrFail($id);
        $mainType->update($request->all());

        return redirect()->route('admin.main-types.index')->with('success', 'Main Type updated successfully.');
    }

    public function destroy($id)
    {
        \App\Models\MainType::destroy($id);
        return response()->json(['success' => true]);
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
            foreach ($rows as $index => $row) {
                // Skip header (baris pertama)
                if ($index === 0) continue;

                // Lewati baris kosong
                if (empty(array_filter($row))) continue;
                
                $subTypeName = trim($row[0] ?? '');
                $mainTypeName = trim($row[1] ?? '');

                if ($subTypeName === '' || $mainTypeName === '') continue;

                // Create or find Main Type
                $mainType = \App\Models\MainType::firstOrCreate(['Main_Type' => $mainTypeName]);
                
                // Create or find Sub Type
                $type = \App\Models\Type::firstOrNew(['Type' => $subTypeName]);
                
                if (!$type->exists) {
                    $maxId = \App\Models\Type::max('Id_Type') ?? 0;
                    $type->Id_Type = $maxId + 1;
                }
                
                $type->Id_Main_Type = $mainType->Id_Main_Type;
                $type->save();

                $imported++;
            }

            return redirect()->route('admin.main-types.index')->with('success', "$imported baris berhasil diimport.");
        } catch (\Exception $e) {
            return redirect()->route('admin.main-types.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
