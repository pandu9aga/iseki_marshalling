<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Type::query();
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.types.edit', $row->Id_Type) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a> ';
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

    public function destroy($id)
    {
        Type::findOrFail($id)->delete();
        return response()->json(['message' => 'Type deleted successfully.']);
    }
}
