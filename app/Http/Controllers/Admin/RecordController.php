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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
            \App\Models\PartKurang::where('id_record', $record->Id_Record)->delete();
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
                if ($rl->record && $rl->record->member) {
                    $memberPhoto = $rl->record->member->photo_employee ? $photoBase . '/' . $rl->record->member->photo_employee : null;
                }
                if ($rl->Reporter_Nik) {
                    $reporter = DB::connection('rifa')->table('employees')->where('nik', $rl->Reporter_Nik)->first(['nama', 'photo_employee']);
                    $reporterPhoto = $reporter && $reporter->photo_employee ? $photoBase . '/' . $reporter->photo_employee : null;
                }
                return [
                    'Id_Record_List' => $rl->Id_Record_List,
                    'Code_Part'      => $rl->Code_Part,
                    'Name_Part'      => $rl->Name_Part,
                    'Code_Rack'      => $rl->Code_Rack,
                    'Box'            => $rl->Box,
                    'Qty'            => $rl->Qty,
                    'Difference'     => $rl->Difference,
                    'sequence'       => $rl->record ? $rl->record->Sequence_No_Record : '-',
                    'production_date' => $rl->record ? $rl->record->Production_Date_Record : '-',
                    'type'           => $rl->record ? $rl->record->Type : '-',
                    'area'           => $rl->record ? ucwords(str_replace('_', ' ', $rl->record->Area)) : '-',
                    'member'         => $rl->record && $rl->record->member ? $rl->record->member->nama : '-',
                    'member_photo'   => $memberPhoto,
                    'reporter_nik'   => $rl->Reporter_Nik ?? '-',
                    'reporter_name'  => $rl->Reporter_Nik ? (DB::connection('rifa')->table('employees')->where('nik', $rl->Reporter_Nik)->value('nama') ?? $rl->Reporter_Nik) : '-',
                    'reporter_photo' => $reporterPhoto,
                    'report_empty'   => $rl->Report_Empty ? \Carbon\Carbon::parse($rl->Report_Empty)->format('d/m/Y H:i') : '-',
                    'report_comment' => $rl->Report_Comment ?? '-',
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

    public function partKurangList(Request $request)
    {
        if ($request->ajax()) {
            $data = \App\Models\PartKurang::with('member')
                ->orderBy('comment_time', 'desc');

            if ($request->filled('filter_date')) {
                $data->whereDate('comment_time', $request->filter_date);
            }

            if ($request->filled('member_id')) {
                $data->where('id_user', $request->member_id);
            }

            if ($request->filled('reporter_nik')) {
                $data->where('perakitan_nik', $request->reporter_nik);
            }

            if ($request->filled('filter_status')) {
                if ($request->filter_status === 'diterima' || $request->filter_status === 'oke') {
                    $data->where('status', 'oke');
                } elseif ($request->filter_status === 'pending') {
                    $data->where('status', 'pending');
                }
            }

            return datatables($data)
                ->addIndexColumn()
                ->addColumn('member_name', function ($row) {
                    return $row->member ? $row->member->nama : '-';
                })
                ->addColumn('sequence_record', function ($row) {
                    return $row->sequence_no ?? '-';
                })
                ->addColumn('production_date', function ($row) {
                    return $row->production_date ?? '-';
                })
                ->addColumn('type_record', function ($row) {
                    return $row->type ?? '-';
                })
                ->addColumn('area_record', function ($row) {
                    return ucwords(str_replace('_', ' ', $row->area ?? '-'));
                })
                ->addColumn('comment_time', function ($row) {
                    return $row->comment_time ? $row->comment_time->format('d/m/Y H:i') : '-';
                })
                ->addColumn('reporter_nik', function ($row) {
                    return $row->perakitan_nik ?? '-';
                })
                ->addColumn('reporter_name', function ($row) {
                    if (!$row->perakitan_nik) return '-';
                    return DB::connection('rifa')->table('employees')->where('nik', $row->perakitan_nik)->value('nama') ?? $row->perakitan_nik;
                })
                ->addColumn('comment', function ($row) {
                    return e($row->comment);
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'oke') {
                        return '<span class="badge bg-success">Oke / Diterima</span>';
                    }
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                })
                ->rawColumns(['status_badge'])
                ->make(true);
        }

        // Ambil daftar member marshalling yang ada di part_kurangs
        $memberUserIds = \App\Models\PartKurang::whereNotNull('id_user')->distinct()->pluck('id_user');
        $marshallingMembers = \App\Models\Member::whereIn('id', $memberUserIds)->orderBy('nama', 'asc')->get(['id', 'nama', 'nik']);

        // Ambil daftar pelapor (perakitan) yang ada di part_kurangs
        $reporterNiks = \App\Models\PartKurang::whereNotNull('perakitan_nik')->distinct()->pluck('perakitan_nik');
        $reporters = DB::connection('rifa')->table('employees')
            ->whereIn('nik', $reporterNiks)
            ->orderBy('nama', 'asc')
            ->get(['nik', 'nama']);

        return view('admin.records.part-kurang', compact('marshallingMembers', 'reporters'));
    }

    public function exportPartKurang(Request $request)
    {
        $query = \App\Models\PartKurang::with(['member', 'record'])
            ->orderBy('comment_time', 'asc');

        $date = $request->filter_date;
        if (!empty($date)) {
            $query->whereDate('comment_time', $date);
        }

        if ($request->filled('member_id')) {
            $query->where('id_user', $request->member_id);
        }

        if ($request->filled('reporter_nik')) {
            $query->where('perakitan_nik', $request->reporter_nik);
        }

        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'diterima' || $request->filter_status === 'oke') {
                $query->where('status', 'oke');
            } elseif ($request->filter_status === 'pending') {
                $query->where('status', 'pending');
            }
        }

        $items = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetTitle = !empty($date) ? 'Part Kurang ' . $date : 'Part Kurang Semua';
        $sheet->setTitle(substr($sheetTitle, 0, 31));

        // Title & Period (Merged so Column A is not stretched by title text)
        $sheet->setCellValue('A1', 'LAPORAN PART KURANG');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $periodText = !empty($date) ? \Carbon\Carbon::parse($date)->translatedFormat('d F Y') : 'Semua Tanggal';
        $sheet->setCellValue('A2', 'Tanggal: ' . $periodText);
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getFont()->setSize(11);

        // Header Table
        $headers = [
            'A4' => 'No',
            'B4' => 'Member Marshalling',
            'C4' => 'Seq Record',
            'D4' => 'Prod Date',
            'E4' => 'Type Traktor',
            'F4' => 'Area',
            'G4' => 'Waktu Marshalling',
            'H4' => 'Waktu Komentar',
            'I4' => 'Reporter NIK',
            'J4' => 'Reporter Nama',
            'K4' => 'Catatan Part Kurang',
            'L4' => 'Status',
            'M4' => 'Waktu Diterima',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header style
        $sheet->getStyle('A4:M4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E91E63'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(26);

        // Data Rows
        $row = 5;
        $no = 1;
        foreach ($items as $item) {
            $reporterName = '-';
            if ($item->perakitan_nik) {
                $reporterName = DB::connection('rifa')
                    ->table('employees')
                    ->where('nik', $item->perakitan_nik)
                    ->value('nama') ?? $item->perakitan_nik;
            }

            $marshallingTime = ($item->record && $item->record->Time_Record) 
                ? \Carbon\Carbon::parse($item->record->Time_Record)->format('d/m/Y H:i') 
                : '-';

            $commentTime = $item->comment_time ? $item->comment_time->format('d/m/Y H:i') : '-';
            $receivedTime = $item->received_time ? $item->received_time->format('d/m/Y H:i') : '-';
            $statusLabel = ($item->status === 'oke') ? 'Sudah Diterima' : 'Pending';

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item->member ? $item->member->nama : '-');
            $sheet->setCellValue('C' . $row, $item->sequence_no ?? '-');
            $sheet->setCellValue('D' . $row, $item->production_date ?? '-');
            $sheet->setCellValue('E' . $row, $item->type ?? '-');
            $sheet->setCellValue('F' . $row, ucwords(str_replace('_', ' ', $item->area ?? '-')));
            $sheet->setCellValue('G' . $row, $marshallingTime);
            $sheet->setCellValue('H' . $row, $commentTime);
            $sheet->setCellValue('I' . $row, $item->perakitan_nik ?? '-');
            $sheet->setCellValue('J' . $row, $reporterName);
            $sheet->setCellValue('K' . $row, $item->comment ?? '-');
            $sheet->setCellValue('L' . $row, $statusLabel);
            $sheet->setCellValue('M' . $row, $receivedTime);

            // Center alignment for some columns
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Status background
            if ($item->status === 'oke') {
                $sheet->getStyle("L{$row}")->getFont()->getColor()->setRGB('198754');
                $sheet->getStyle("L{$row}")->getFont()->setBold(true);
            } else {
                $sheet->getStyle("L{$row}")->getFont()->getColor()->setRGB('D39E00');
                $sheet->getStyle("L{$row}")->getFont()->setBold(true);
            }

            $row++;
            $no++;
        }

        $lastRow = max(5, $row - 1);

        // Borders
        $sheet->getStyle("A4:M{$lastRow}")->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
        ]);

        // Auto-fit column widths (kecuali kolom A diberi width pas untuk nomor)
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(8);
        foreach (range('B', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Aktifkan Filter di header tabel
        $sheet->setAutoFilter("A4:M{$lastRow}");

        $writer = new Xlsx($spreadsheet);
        $fileName = !empty($date) ? ('Laporan_Part_Kurang_' . $date . '.xlsx') : ('Laporan_Part_Kurang_Semua_' . now()->format('Ymd_His') . '.xlsx');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function partKurangCarousel(Request $request)
    {
        $photoBase = '/iseki_rifa/public/photo_employee';

        // Tampilkan semua part kurang dari tabel part_kurangs yang berstatus pending entah tanggal berapapun
        $items = \App\Models\PartKurang::with(['member', 'record'])
            ->where('status', 'pending')
            ->orderBy('comment_time', 'desc')
            ->get()
            ->map(function ($pk) use ($photoBase) {
                $memberPhoto = null;
                $perakitanPhoto = null;
                $perakitanName = null;
                $memberAudio = null;

                if ($pk->member) {
                    $memberPhoto = $pk->member->photo_employee ? $photoBase . '/' . $pk->member->photo_employee : null;

                    // Ambil audio nama member dari member_areas berdasarkan NIK (dan area jika cocok)
                    $ma = \App\Models\MemberArea::where('nik', $pk->member->nik)
                        ->whereNotNull('audio_name')
                        ->where('audio_name', '!=', '')
                        ->orderByRaw("FIELD(area, '{$pk->area}') DESC")
                        ->first();

                    if ($ma && file_exists(public_path('assets/sounds/members/' . $ma->audio_name))) {
                        $memberAudio = asset('assets/sounds/members/' . $ma->audio_name);
                    }
                }

                if ($pk->perakitan_nik) {
                    $emp = DB::connection('rifa')->table('employees')->where('nik', $pk->perakitan_nik)->first(['nama', 'photo_employee']);
                    if ($emp) {
                        $perakitanName = $emp->nama;
                        $perakitanPhoto = $emp->photo_employee ? $photoBase . '/' . $emp->photo_employee : null;
                    } else {
                        $perakitanName = $pk->perakitan_nik;
                    }
                }

                return [
                    'Id_Record'              => $pk->id_record,
                    'Id_Part_Kurang'         => $pk->id,
                    'sequence'               => $pk->sequence_no,
                    'production_date'        => $pk->production_date,
                    'type'                   => $pk->type,
                    'area'                   => ucwords(str_replace('_', ' ', $pk->area)),
                    'time_record'            => ($pk->record && $pk->record->Time_Record) ? \Carbon\Carbon::parse($pk->record->Time_Record)->format('d/m/Y H:i') : '-',
                    'member_name'            => $pk->member ? $pk->member->nama : '-',
                    'member_nik'             => $pk->member ? ($pk->member->nik ?? '-') : ($pk->member_nik ?? '-'),
                    'member_photo'           => $memberPhoto,
                    'member_audio'           => $memberAudio,
                    'perakitan_comment'      => $pk->comment,
                    'perakitan_nik'          => $pk->perakitan_nik ?? '-',
                    'perakitan_name'         => $perakitanName ?? ($pk->perakitan_nik ?? '-'),
                    'perakitan_photo'        => $perakitanPhoto,
                    'perakitan_comment_time' => $pk->comment_time ? $pk->comment_time->format('d/m/Y H:i') : '-',
                    'status'                 => $pk->status,
                ];
            });

        return response()->json($items);
    }
}
