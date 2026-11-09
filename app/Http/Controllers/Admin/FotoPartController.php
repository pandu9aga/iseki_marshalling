<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marshalling;
use App\Models\Type;
use Illuminate\Http\Request;

class FotoPartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Type::with('mainType')->withCount('marshallings')->with(['marshallings' => function ($q) {
                $q->select('Id_Type', 'Area')->distinct();
            }]);
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('main_type', function ($row) {
                    return $row->mainType ? $row->mainType->Main_Type : '-';
                })
                ->addColumn('location_areas', function ($row) {
                    $areas = $row->marshallings->pluck('Area')->unique()->filter()->values();
                    if ($areas->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }
                    return $areas->map(function ($area) use ($row) {
                        $label = ucwords(str_replace('_', ' ', $area));
                        $url = route('admin.foto-parts.boxes', ['type' => $row->Id_Type, 'area' => $area]);
                        return '<a href="' . $url . '" class="badge bg-light text-dark border me-1 mb-1 text-decoration-none" title="Lihat box di ' . $label . '">' . $label . '</a>';
                    })->implode(' ');
                })
                ->addColumn('list_marshalling', function ($row) {
                    return $row->marshallings_count;
                })
                ->rawColumns(['location_areas'])
                ->make(true);
        }
        return view('admin.foto_parts.index');
    }

    public function boxes($type, $area)
    {
        $type = Type::with('mainType')->findOrFail($type);

        $boxes = \App\Models\Marshalling::where('Id_Type', $type->Id_Type)
            ->where('Area', $area)
            ->select('Box')
            ->distinct()
            ->orderBy('Box')
            ->where('marshallings.Is_Active', true)
            ->pluck('Box');

        return view('admin.foto_parts.boxes', [
            'type' => $type,
            'area' => $area,
            'boxes' => $boxes,
        ]);
    }

    public function parts($type, $area, $box)
    {
        $type = Type::with('mainType')->findOrFail($type);

        $marshallings = Marshalling::where('Id_Type', $type->Id_Type)
            ->where('Area', $area)
            ->where('Box', $box)
            ->where('marshallings.Is_Active', true)
            ->orderBy('Sequence_No')
            ->get();

        return view('admin.foto_parts.parts', [
            'type' => $type,
            'area' => $area,
            'box' => $box,
            'marshallings' => $marshallings,
        ]);
    }
}
