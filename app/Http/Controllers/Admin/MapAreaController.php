<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapArea;
use Illuminate\Http\Request;

class MapAreaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MapArea::orderBy('Sequence_No', 'asc');
            if ($request->has('area') && $request->area != '') {
                $query->where('Area', $request->area);
            }
            $data = $query->get();
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<button type="button" class="btn btn-warning btn-sm edit-btn" data-id="' . $row->id . '" data-area="' . htmlspecialchars($row->Area) . '" data-location="' . htmlspecialchars($row->Location_Rack) . '" data-sequence="' . $row->Sequence_No . '"><i class="fas fa-edit"></i></button> ';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.map-areas.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Area' => 'required',
            'Location_Rack' => 'required',
            'Sequence_No' => 'required|integer',
        ]);

        MapArea::updateOrCreate(
            ['Area' => $request->Area, 'Sequence_No' => $request->Sequence_No],
            ['Location_Rack' => $request->Location_Rack]
        );

        return response()->json(['message' => 'Map Area saved successfully.']);
    }

    public function update(Request $request, $id)
    {
        $mapArea = MapArea::findOrFail($id);
        
        $request->validate([
            'Area' => 'required',
            'Location_Rack' => 'required',
            'Sequence_No' => 'required|integer',
        ]);

        $mapArea->update($request->all());

        return response()->json(['message' => 'Map Area updated successfully.']);
    }

    public function destroy($id)
    {
        MapArea::findOrFail($id)->delete();
        return response()->json(['message' => 'Map Area deleted successfully.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx',
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
            // Assuming first row is header: Area, Location_Rack, Sequence_No
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header

                $area = trim($row[0] ?? '');
                $location = trim($row[1] ?? '');
                $sequence = trim($row[2] ?? '');

                if ($area === '' || $sequence === '') continue;

                MapArea::updateOrCreate(
                    ['Area' => $area, 'Sequence_No' => $sequence],
                    ['Location_Rack' => $location]
                );
                
                $imported++;
            }

            return redirect()->route('admin.map-areas.index')
                ->with('success', "$imported Map Areas imported successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.map-areas.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
