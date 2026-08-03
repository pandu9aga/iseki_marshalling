<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Punishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PunishmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Punishment::query();
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    $member = DB::connection('rifa')
                        ->table('employees')
                        ->where('nik', $row->nik)
                        ->first();
                    return $member ? $member->nama : '-';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.punishments.index');
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

        $member = DB::connection('rifa')
            ->table('employees')
            ->where('nik', $nik)
            ->first();

        if (!$member) {
            return redirect()->back()->with('error', 'NIK tidak ditemukan di data member.');
        }

        $exists = Punishment::where('nik', $nik)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'NIK sudah terdaftar di punishment.');
        }

        Punishment::create(['nik' => $nik]);

        return redirect()->back()->with('success', 'NIK ' . $nik . ' (' . $member->nama . ') ditambahkan.');
    }

    public function destroy($id)
    {
        Punishment::findOrFail($id)->delete();
        return response()->json(['message' => 'Punishment deleted successfully.']);
    }
}