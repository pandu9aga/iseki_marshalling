<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberAreaController extends Controller
{
    protected $validAreas = ['sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle'];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MemberArea::query();
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    $member = DB::connection('rifa')
                        ->table('employees')
                        ->where('nik', $row->nik)
                        ->first();
                    return $member ? $member->nama : '-';
                })
                ->editColumn('area', function ($row) {
                    return ucwords(str_replace('_', ' ', $row->area));
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.member_areas.index', ['validAreas' => $this->validAreas]);
    }

    public function search(Request $request)
    {
        $term = trim($request->query('q', ''));
        if ($term === '') {
            return response()->json([]);
        }

        $members = DB::connection('rifa')
            ->table('employees')
            ->where('nama', 'like', '%' . $term . '%')
            ->limit(15)
            ->get(['id', 'nik', 'nama']);

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $nik = trim($request->input('nik'));
        $area = trim($request->input('area'));

        if (!in_array($area, $this->validAreas)) {
            return redirect()->back()->with('error', 'Area tidak valid.');
        }

        $member = DB::connection('rifa')
            ->table('employees')
            ->where('nik', $nik)
            ->first();

        if (!$member) {
            return redirect()->back()->with('error', 'NIK tidak ditemukan di data member.');
        }

        $exists = MemberArea::where('nik', $nik)->where('area', $area)->exists();
        if ($exists) {
            return redirect()->back()->with('error', $member->nama . ' sudah terdaftar di area ' . ucwords(str_replace('_', ' ', $area)) . '.');
        }

        MemberArea::create([
            'nik' => $nik,
            'area' => $area,
        ]);

        return redirect()->back()->with('success', $member->nama . ' ditambahkan ke area ' . ucwords(str_replace('_', ' ', $area)) . '.');
    }

    public function destroy($id)
    {
        MemberArea::findOrFail($id)->delete();
        return response()->json(['message' => 'Member area deleted successfully.']);
    }
}