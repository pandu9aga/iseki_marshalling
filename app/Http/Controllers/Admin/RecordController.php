<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\Type;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Record::with(['recordLists', 'member'])
                ->orderBy('Time_Record', 'desc');

            if ($request->filled('filter_date')) {
                $data->whereDate('Time_Record', $request->filter_date);
            }
            if ($request->filled('filter_member')) {
                $data->where('Id_User', $request->filter_member);
            }
            if ($request->filled('filter_area')) {
                $data->where('Area', $request->filter_area);
            }
            if ($request->filled('filter_type')) {
                $data->where('Type', $request->filter_type);
            }

            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    return $row->member ? $row->member->nama : '-';
                })
                ->addColumn('status', function ($row) {
                    $total = $row->recordLists->count();
                    $completed = $row->recordLists->whereNotNull('Time_Record')->count();
                    return "$completed / $total";
                })
                ->editColumn('Area', function($record) {
                    return $record->Area ? ucwords(str_replace('_', ' ', $record->Area)) : '-';
                })
                ->addColumn('remark', function ($row) {
                    return $row->Remark ? '<span title="'.e($row->Remark).'">'.e(\Illuminate\Support\Str::limit($row->Remark, 40)).'</span>' : '-';
                })
                ->rawColumns(['remark'])
                ->make(true);
        }

        $members = Member::orderBy('nama')->get();
        $types = Type::orderBy('Type')->get();
        $areas = Record::select('Area')->distinct()->whereNotNull('Area')->orderBy('Area')->pluck('Area');

        return view('admin.records.index', compact('members', 'types', 'areas'));
    }

    public function show($id)
    {
        $record = Record::with(['recordLists', 'member'])->findOrFail($id);
        return response()->json($record);
    }

    public function ngList(Request $request)
    {
        if ($request->ajax()) {
            $data = Record_List::with(['record.member'])
                ->where('Mode', 'ai')
                ->whereNotNull('Time_Record')
                ->whereColumn('Qty_Record', '!=', 'Qty')
                ->where(function ($q) {
                    $q->whereNull('Status_Ng')
                      ->orWhere('Status_Ng', '!=', 'ng_ok');
                })
                ->orderBy('Time_Record', 'desc');

            if ($request->filled('filter_date')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->whereDate('Time_Record', $request->filter_date);
                });
            }
            if ($request->filled('filter_member')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->where('Id_User', $request->filter_member);
                });
            }
            if ($request->filled('filter_area')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->where('Area', $request->filter_area);
                });
            }
            if ($request->filled('filter_type')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->where('Type', $request->filter_type);
                });
            }

            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    return $row->record && $row->record->member ? $row->record->member->nama : '-';
                })
                ->addColumn('sequence_record', function ($row) {
                    return $row->record ? $row->record->Sequence_No_Record : '-';
                })
                ->addColumn('production_date', function ($row) {
                    return $row->record ? $row->record->Production_Date_Record : '-';
                })
                ->addColumn('type_record', function ($row) {
                    return $row->record ? $row->record->Type : '-';
                })
                ->addColumn('area_record', function ($row) {
                    return $row->record ? ucwords(str_replace('_', ' ', $row->record->Area)) : '-';
                })
                ->addColumn('time_record', function ($row) {
                    return $row->Time_Record ?? '-';
                })
                ->make(true);
        }

        $members = Member::orderBy('nama')->get();
        $types = Type::orderBy('Type')->get();
        $areas = Record::select('Area')->distinct()->whereNotNull('Area')->orderBy('Area')->pluck('Area');

        return view('admin.records.ng', compact('members', 'types', 'areas'));
    }

    public function ngDetail($recordListId)
    {
        $recordList = Record_List::with('record.member')->findOrFail($recordListId);
        return response()->json($recordList);
    }

    public function approveNg($recordListId)
    {
        $recordList = Record_List::findOrFail($recordListId);
        $recordList->update([
            'Status_Ng' => 'ng_ok',
        ]);

        return response()->json(['success' => true, 'status' => 'ng_ok']);
    }

    public function emptyPart(Request $request)
    {
        if ($request->ajax()) {
            $data = Record_List::with(['record.member'])
                ->where('Is_Empty', 1)
                ->orderBy('Time_Record', 'desc');

            if ($request->filled('filter_date')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->whereDate('Time_Record', $request->filter_date);
                });
            }
            if ($request->filled('filter_member')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->where('Id_User', $request->filter_member);
                });
            }
            if ($request->filled('filter_area')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->where('Area', $request->filter_area);
                });
            }
            if ($request->filled('filter_type')) {
                $data->whereHas('record', function ($q) use ($request) {
                    $q->where('Type', $request->filter_type);
                });
            }

            $data->where('Is_Empty', 1);

            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    return $row->record && $row->record->member ? $row->record->member->nama : '-';
                })
                ->addColumn('sequence_record', function ($row) {
                    return $row->record ? $row->record->Sequence_No_Record : '-';
                })
                ->addColumn('production_date', function ($row) {
                    return $row->record ? $row->record->Production_Date_Record : '-';
                })
                ->addColumn('type_record', function ($row) {
                    return $row->record ? $row->record->Type : '-';
                })
                ->addColumn('area_record', function ($row) {
                    return $row->record ? ucwords(str_replace('_', ' ', $row->record->Area)) : '-';
                })
                ->addColumn('time_record', function ($row) {
                    return $row->Time_Record ?? '-';
                })
                ->make(true);
        }

        $members = Member::orderBy('nama')->get();
        $types = Type::orderBy('Type')->get();
        $areas = Record::select('Area')->distinct()->whereNotNull('Area')->orderBy('Area')->pluck('Area');

        return view('admin.records.empty-part', compact('members', 'types', 'areas'));
    }
}
