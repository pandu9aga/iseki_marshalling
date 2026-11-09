<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarshallingSetting;
use App\Models\ScanOrderOverride;
use Illuminate\Http\Request;

class MarshallingSettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables(ScanOrderOverride::query())
                ->addIndexColumn()
                ->editColumn('mode', fn ($row) => $row->mode === 'box' ? 'Per Box' : 'Per Sequence')
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.marshalling_settings.index', ['scanOrder' => MarshallingSetting::scanOrder()]);
    }

    public function updateDefault(Request $request)
    {
        $request->validate(['scan_order' => 'required|in:box,sequence']);

        MarshallingSetting::updateOrCreate(['key' => 'scan_order'], ['value' => $request->scan_order]);

        return redirect()->back()->with('success', 'Urutan scan default disimpan.');
    }

    public function storeOverride(Request $request)
    {
        $request->merge(['sequence_no' => trim((string) $request->input('sequence_no'))]);
        $data = $request->validate([
            'sequence_no' => 'required|string|max:50|unique:scan_order_overrides,sequence_no',
            'mode' => 'required|in:box,sequence',
            'keterangan' => 'nullable|string|max:255',
        ]);

        ScanOrderOverride::create($data);

        return redirect()->back()->with('success', 'Pengecualian untuk No. Sequence ' . $data['sequence_no'] . ' ditambahkan.');
    }

    public function destroyOverride(ScanOrderOverride $override)
    {
        $override->delete();
        return response()->json(['message' => 'Pengecualian dihapus.']);
    }
}
