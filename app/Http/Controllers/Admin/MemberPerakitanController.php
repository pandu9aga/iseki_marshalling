<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marshalling;
use App\Models\MemberPerakitan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberPerakitanController extends Controller
{
    protected $validAreas = [];

    public function __construct()
    {
        $this->validAreas = Marshalling::select(['Area', 'Box'])
            ->get()
            ->groupBy('Area')
            ->map(fn ($items) => $items->pluck('Box')->unique()->values()->toArray())
            ->toArray();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MemberPerakitan::query();
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
                    $html = '<div class="d-flex gap-1">';
                    $html .= '<button type="button" class="btn btn-outline-info btn-sm edit-btn" data-id="' . $row->id . '" data-area="' . e($row->area) . '" data-box="' . e($row->box) . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $html .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.member_perakitans.index', ['validAreas' => $this->validAreas]);
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
        $request->validate([
            'nik'  => 'required|string',
            'area' => 'required|string',
            'box'  => 'required|string',
        ]);

        $nik = trim($request->input('nik'));
        $area = trim($request->input('area'));
        $box = trim($request->input('box'));

        if (!array_key_exists($area, $this->validAreas)) {
            return redirect()->back()->with('error', 'Area tidak valid.');
        }

        if (!in_array($box, $this->validAreas[$area])) {
            return redirect()->back()->with('error', 'Box tidak valid untuk area tersebut.');
        }

        $member = DB::connection('rifa')
            ->table('employees')
            ->where('nik', $nik)
            ->first();

        if (!$member) {
            return redirect()->back()->with('error', 'NIK tidak ditemukan di data member.');
        }

        $exists = MemberPerakitan::where('nik', $nik)->where('area', $area)->where('box', $box)->exists();
        if ($exists) {
            return redirect()->back()->with('error', $member->nama . ' sudah terdaftar di area ' . ucwords(str_replace('_', ' ', $area)) . ' box ' . $box . '.');
        }

        MemberPerakitan::create([
            'nik'  => $nik,
            'area' => $area,
            'box'  => $box,
        ]);

        return redirect()->back()->with('success', $member->nama . ' ditambahkan ke area ' . ucwords(str_replace('_', ' ', $area)) . ' box ' . $box . '.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'area' => 'required|string',
            'box'  => 'required|string',
        ]);

        $memberPerakitan = MemberPerakitan::findOrFail($id);

        $area = trim($request->input('area'));
        $box = trim($request->input('box'));

        if (!array_key_exists($area, $this->validAreas)) {
            return response()->json(['message' => 'Area tidak valid.'], 422);
        }

        if (!in_array($box, $this->validAreas[$area])) {
            return response()->json(['message' => 'Box tidak valid untuk area tersebut.'], 422);
        }

        $exists = MemberPerakitan::where('nik', $memberPerakitan->nik)
            ->where('area', $area)
            ->where('box', $box)
            ->where('id', '!=', $memberPerakitan->id)
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Member sudah terdaftar di area & box tersebut.'], 422);
        }

        $memberPerakitan->update([
            'area' => $area,
            'box'  => $box,
        ]);

        return response()->json(['message' => 'Member perakitan berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $memberPerakitan = MemberPerakitan::findOrFail($id);
        $memberPerakitan->delete();
        return response()->json(['message' => 'Member perakitan deleted successfully.']);
    }
}
