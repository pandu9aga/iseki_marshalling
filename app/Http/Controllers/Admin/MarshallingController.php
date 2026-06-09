<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marshalling;
use App\Models\Type;
use Illuminate\Http\Request;

class MarshallingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Marshalling::with('type');
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('type_name', function ($row) {
                    return $row->type ? $row->type->Type : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.marshallings.edit', $row->Id_Marshalling) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a> ';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->Id_Marshalling . '"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $types = Type::all();
        return view('admin.marshallings.index', compact('types'));
    }

    public function create()
    {
        $types = Type::all();
        return view('admin.marshallings.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Type' => 'required|exists:types,Id_Type',
            'Sequence_No' => 'required|integer',
            'Code_Part' => 'required',
            'Name_Part' => 'required',
            'Code_Rack' => 'required',
            'Difference' => 'required',
            'Location_Rack' => 'required',
            'Box' => 'required',
            'Qty' => 'required|integer',
            'Mode' => 'required|in:manual,ai',
            'Area' => 'required|in:sub_assy,sub_engine,transmisi,main_line,mowcol,front_axle',
        ]);

        Marshalling::create($request->all());
        return redirect()->route('admin.marshallings.index')->with('success', 'Marshalling created successfully.');
    }

    public function edit($id)
    {
        $marshalling = Marshalling::findOrFail($id);
        $types = Type::all();
        return view('admin.marshallings.edit', compact('marshalling', 'types'));
    }

    public function update(Request $request, $id)
    {
        $marshalling = Marshalling::findOrFail($id);
        $request->validate([
            'Id_Type' => 'required|exists:types,Id_Type',
            'Sequence_No' => 'required|integer',
            'Code_Part' => 'required',
            'Name_Part' => 'required',
            'Code_Rack' => 'required',
            'Difference' => 'required',
            'Location_Rack' => 'required',
            'Box' => 'required',
            'Qty' => 'required|integer',
            'Mode' => 'required|in:manual,ai',
            'Area' => 'required|in:sub_assy,sub_engine,transmisi,main_line,mowcol,front_axle',
        ]);

        $marshalling->update($request->all());
        return redirect()->route('admin.marshallings.index')->with('success', 'Marshalling updated successfully.');
    }

    public function destroy($id)
    {
        Marshalling::findOrFail($id)->delete();
        return response()->json(['message' => 'Marshalling deleted successfully.']);
    }
}
