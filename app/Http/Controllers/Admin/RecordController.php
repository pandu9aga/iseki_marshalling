<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    private function canDeleteRecord(): bool
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return false;
        }
        $name = strtolower(trim($admin->name));
        return in_array($name, ['aga', 'saiful'], true);
    }

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

            $canDelete = $this->canDeleteRecord();

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
                ->addColumn('action', function ($row) use ($canDelete) {
                    if ($canDelete) {
                        return '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="'.$row->Id_Record.'" title="Delete Record"><i class="fas fa-trash"></i></button>';
                    }
                    return '-';
                })
                ->rawColumns(['remark', 'action'])
                ->make(true);
        }

        $members = Member::orderBy('nama')->get();
        $types = Type::orderBy('Type')->get();
        $areas = Record::select('Area')->distinct()->whereNotNull('Area')->orderBy('Area')->pluck('Area');
        $canDelete = $this->canDeleteRecord();

        return view('admin.records.index', compact('members', 'types', 'areas', 'canDelete'));
    }

    public function show($id)
    {
        $record = Record::with(['recordLists', 'member'])->findOrFail($id);
        return response()->json($record);
    }

    public function destroy($id)
    {
        if (!$this->canDeleteRecord()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus record.'
            ], 403);
        }

        $record = Record::findOrFail($id);

        DB::transaction(function () use ($record) {
            $recordLists = Record_List::where('Id_Record', $record->Id_Record)->get();
            foreach ($recordLists as $rl) {
                if ($rl->Image_Ng && file_exists(public_path($rl->Image_Ng))) {
                    @unlink(public_path($rl->Image_Ng));
                }
            }
            Record_List::where('Id_Record', $record->Id_Record)->delete();
            $record->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Record successfully deleted.'
        ]);
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

    public function reportEmptyList(Request $request)
    {
        if ($request->ajax()) {
            $data = Record_List::with(['record.member'])
                ->whereNotNull('Report_Empty')
                ->orderBy('Report_Empty', 'desc');

            if ($request->filled('filter_date')) {
                $data->whereDate('Report_Empty', $request->filter_date);
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
                ->addColumn('report_empty_time', function ($row) {
                    return $row->Report_Empty ? \Carbon\Carbon::parse($row->Report_Empty)->format('d/m/Y H:i') : '-';
                })
                ->addColumn('reporter_nik', function ($row) {
                    return $row->Reporter_Nik ?? '-';
                })
                ->addColumn('report_comment', function ($row) {
                    return $row->Report_Comment ? e($row->Report_Comment) : '-';
                })
                ->addColumn('perakitan_comment', function ($row) {
                    return $row->record && $row->record->Perakitan_Comment ? e($row->record->Perakitan_Comment) : '-';
                })
                ->make(true);
        }

        $today = now()->format('Y-m-d');
        return view('admin.records.report-empty', compact('today'));
    }

    public function carouselData(Request $request)
    {
        $date = $request->date ?: now()->format('Y-m-d');
        $photoBase = '/iseki_rifa/public/photo_employee';

        $items = Record_List::with(['record.member'])
            ->whereNotNull('Report_Empty')
            ->whereDate('Report_Empty', $date)
            ->orderBy('Report_Empty', 'desc')
            ->get()
            ->map(function ($rl) use ($photoBase) {
                $memberPhoto = null;
                $reporterPhoto = null;
                $perakitanPhoto = null;
                $perakitanName = null;

                if ($rl->record && $rl->record->member) {
                    $memberPhoto = $rl->record->member->photo_employee ? $photoBase . '/' . $rl->record->member->photo_employee : null;
                }
                if ($rl->Reporter_Nik) {
                    $reporter = DB::connection('rifa')->table('employees')->where('nik', $rl->Reporter_Nik)->first(['nama', 'photo_employee']);
                    $reporterPhoto = $reporter && $reporter->photo_employee ? $photoBase . '/' . $reporter->photo_employee : null;
                }
                if ($rl->record && $rl->record->Perakitan_Nik) {
                    $perakitanUser = DB::connection('rifa')->table('employees')->where('nik', $rl->record->Perakitan_Nik)->first(['nama', 'photo_employee']);
                    if ($perakitanUser) {
                        $perakitanName = $perakitanUser->nama;
                        $perakitanPhoto = $perakitanUser->photo_employee ? $photoBase . '/' . $perakitanUser->photo_employee : null;
                    } else {
                        $perakitanName = $rl->record->Perakitan_Nik;
                    }
                }

                return [
                    'Id_Record_List'         => $rl->Id_Record_List,
                    'Code_Part'              => $rl->Code_Part,
                    'Name_Part'              => $rl->Name_Part,
                    'Code_Rack'              => $rl->Code_Rack,
                    'Box'                    => $rl->Box,
                    'Qty'                    => $rl->Qty,
                    'Difference'             => $rl->Difference,
                    'sequence'               => $rl->record ? $rl->record->Sequence_No_Record : '-',
                    'production_date'        => $rl->record ? $rl->record->Production_Date_Record : '-',
                    'type'                   => $rl->record ? $rl->record->Type : '-',
                    'area'                   => $rl->record ? ucwords(str_replace('_', ' ', $rl->record->Area)) : '-',
                    'member'                 => $rl->record && $rl->record->member ? $rl->record->member->nama : '-',
                    'member_nik'             => $rl->record && $rl->record->member ? ($rl->record->member->nik ?? '-') : '-',
                    'member_photo'           => $memberPhoto,
                    'reporter_nik'           => $rl->Reporter_Nik ?? '-',
                    'reporter_name'          => $rl->Reporter_Nik ? (DB::connection('rifa')->table('employees')->where('nik', $rl->Reporter_Nik)->value('nama') ?? $rl->Reporter_Nik) : '-',
                    'reporter_photo'         => $reporterPhoto,
                    'report_empty'           => $rl->Report_Empty ? \Carbon\Carbon::parse($rl->Report_Empty)->format('d/m/Y H:i') : '-',
                    'report_comment'         => $rl->Report_Comment ?? '-',
                    'perakitan_comment'      => $rl->record ? $rl->record->Perakitan_Comment : null,
                    'perakitan_nik'          => $rl->record ? $rl->record->Perakitan_Nik : null,
                    'perakitan_name'         => $perakitanName,
                    'perakitan_photo'        => $perakitanPhoto,
                    'perakitan_comment_time' => ($rl->record && $rl->record->Perakitan_Comment_Time) ? \Carbon\Carbon::parse($rl->record->Perakitan_Comment_Time)->format('d/m/Y H:i') : null,
                ];
            });

        return response()->json($items);
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
