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
                ->addColumn('audio_preview', function ($row) {
                    if ($row->audio_name && file_exists(public_path('assets/sounds/members/' . $row->audio_name))) {
                        $audioUrl = asset('assets/sounds/members/' . $row->audio_name);
                        return '<div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-outline-primary btn-sm play-audio-btn py-0 px-2" data-audio="' . $audioUrl . '" title="Putar Suara">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <small class="text-muted text-truncate" style="max-width:120px;" title="' . e($row->audio_name) . '">' . e($row->audio_name) . '</small>
                        </div>';
                    }
                    return '<span class="badge bg-light text-muted border">Belum ada</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="d-flex gap-1">';
                    $html .= '<button type="button" class="btn btn-outline-info btn-sm upload-audio-btn" data-id="' . $row->id . '" data-nik="' . $row->nik . '" title="Upload/Ganti Suara"><i class="fas fa-microphone"></i></button>';
                    $html .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['audio_preview', 'action'])
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
        $request->validate([
            'nik'   => 'required|string',
            'area'  => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a,mpga|max:5120',
        ]);

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

        $audioName = null;
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $ext = $file->getClientOriginalExtension() ?: 'mp3';
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($member->nama));
            $audioName = $nik . '_' . $safeName . '_' . time() . '.' . $ext;
            $file->move(public_path('assets/sounds/members'), $audioName);
        }

        MemberArea::create([
            'nik'        => $nik,
            'area'       => $area,
            'audio_name' => $audioName,
        ]);

        return redirect()->back()->with('success', $member->nama . ' ditambahkan ke area ' . ucwords(str_replace('_', ' ', $area)) . '.');
    }

    public function uploadAudio(Request $request, $id)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,m4a,mpga|max:5120',
        ]);

        $memberArea = MemberArea::findOrFail($id);
        $member = DB::connection('rifa')->table('employees')->where('nik', $memberArea->nik)->first();

        // Hapus file lama jika ada
        if ($memberArea->audio_name && file_exists(public_path('assets/sounds/members/' . $memberArea->audio_name))) {
            @unlink(public_path('assets/sounds/members/' . $memberArea->audio_name));
        }

        $file = $request->file('audio');
        $ext = $file->getClientOriginalExtension() ?: 'mp3';
        $safeName = $member ? preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($member->nama)) : 'member';
        $audioName = $memberArea->nik . '_' . $safeName . '_' . time() . '.' . $ext;
        $file->move(public_path('assets/sounds/members'), $audioName);

        $memberArea->update(['audio_name' => $audioName]);

        return response()->json([
            'success' => true,
            'message' => 'Audio pelafalan nama berhasil diupload.',
            'audio_name' => $audioName,
            'audio_url' => asset('assets/sounds/members/' . $audioName),
        ]);
    }

    public function destroy($id)
    {
        $memberArea = MemberArea::findOrFail($id);
        if ($memberArea->audio_name && file_exists(public_path('assets/sounds/members/' . $memberArea->audio_name))) {
            @unlink(public_path('assets/sounds/members/' . $memberArea->audio_name));
        }
        $memberArea->delete();
        return response()->json(['message' => 'Member area deleted successfully.']);
    }
}