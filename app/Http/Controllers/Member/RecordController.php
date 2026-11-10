<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\Marshalling;
use App\Models\RecordPending;
use App\Models\MarshallingSetting;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $member = Auth::guard('member')->user();
        // Riwayat hari ini: record (pemilik atau helper) yang dibuat/di-scan hari ini, atau masih di-pending member ini
        $today = Carbon::today();
        $data = Record::with(['recordLists', 'pendings' => fn ($q) => $q->where('Id_User', $member->id)->whereNull('Resumed_At')])
            ->where(fn ($q) => $q->where('Id_User', $member->id)
                ->orWhereHas('recordLists', fn ($r) => $r->where('Assigned_To', $member->id)))
            ->where(fn ($q) => $q->where('Time_Record', '>=', $today)
                ->orWhereHas('recordLists', fn ($r) => $r->where('Time_Record', '>=', $today))
                ->orWhereHas('pendings', fn ($p) => $p->where('Id_User', $member->id)->whereNull('Resumed_At')))
            ->orderBy('Time_Record', 'desc');
        $statusOf = fn ($r) => $r->pendings->isNotEmpty() ? 'Pending'
            : ($r->recordLists->whereNull('Time_Record')->isEmpty() ? 'Selesai' : 'Dikerjakan');

        if ($request->ajax()) {
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) use ($statusOf) {
                    $total = $row->recordLists->count();
                    $completed = $row->recordLists->whereNotNull('Time_Record')->count();
                    $badge = ['Selesai' => 'success', 'Dikerjakan' => 'primary', 'Pending' => 'warning'][$statusOf($row)];
                    return '<span class="badge bg-' . $badge . '">' . $statusOf($row) . "</span> $completed / $total";
                })
                ->addColumn('peran', function ($row) use ($member) {
                    if ($row->Id_User == $member->id) {
                        return 'Pemilik';
                    }
                    $parts = $row->scannersSummary()->firstWhere('id', $member->id)['parts'] ?? 0;

                    return "Helper ($parts part)";
                })
                ->rawColumns(['status'])
                ->editColumn('Area', function($record) {
                    return $record->Area ? ucwords(str_replace('_', ' ', $record->Area)) : '-';
                })
                ->setRowId(function ($row) {
                    return $row->Id_Record;
                })
                ->make(true);
        }

        $counts = (clone $data)->get()->countBy($statusOf);
        return view('member.records.index', compact('counts'));
    }

    public function create()
    {
        $member = Auth::guard('member')->user();

        $duplicateKanban = null;
        $existingRecord = null;
        $existingMember = null;
        $matchedAreas = [];
        $helpBoxes = [];
        $existingRecordId = null;
        $dupTakeovers = [];

        if (session()->has('duplicate_kanban')) {
            $duplicateKanban = session('duplicate_kanban');
            $existingRecord = session('existing_record');
            $existingMember = session('existing_member');
            $matchedAreas = session('matched_areas', []);
            $helpBoxes = session('help_boxes', []);
            $existingRecordId = session('existing_record_id');
            $dupTakeovers = session('takeovers', []);
            session()->forget(['duplicate_kanban', 'existing_record', 'existing_member', 'matched_areas', 'help_boxes', 'existing_record_id', 'takeovers']);
        }

        $helpNext = session('help_next');

        $remarkRecord = null;
        $remarkId = session('remark_record_id');
        if ($remarkId) {
            $remarkRecord = Record::find($remarkId);
            session()->forget('remark_record_id');
        }

        // Record yang di-pending member ini dilewati saat auto-lanjut
        // Fitur pending nonaktif: semua record kembali auto-lanjut, tidak ada kartu pending/ambil alih
        $pendingOn = config('app.pending_enabled');
        $pendingRecordIds = $pendingOn ? RecordPending::where('Id_User', $member->id)->whereNull('Resumed_At')->pluck('Id_Record') : collect();

        if (!$duplicateKanban && !$remarkRecord) {
            $incompleteRecord = Record::where('Id_User', $member->id)
                ->whereNotIn('Id_Record', $pendingRecordIds)
                ->whereHas('recordLists', function ($q) {
                    $q->whereNull('Time_Record')->whereNull('Assigned_To');
                })
                ->orderBy('Time_Record', 'desc')
                ->first();

            // Bukan pemilik: lanjutkan record yang box-nya dibantu member ini
            if (!$incompleteRecord) {
                $helpList = Record_List::where('Assigned_To', $member->id)->whereNull('Time_Record')
                    ->whereNotIn('Id_Record', $pendingRecordIds)->orderBy('Id_Record_List')->first();
                $incompleteRecord = $helpList ? Record::find($helpList->Id_Record) : null;
            }

            if ($incompleteRecord) {
                $nextList = $this->nextPart($incompleteRecord->Id_Record, $member->id, $incompleteRecord->Id_User == $member->id);

                if ($nextList) {
                    return redirect()->route('member.record.scan-part', [
                        $incompleteRecord->Id_Record,
                        $nextList->Id_Record_List
                    ]);
                }
            }
        }

        $pendings = !$pendingOn ? collect() : RecordPending::with('record')->where('Id_User', $member->id)->whereNull('Resumed_At')->orderBy('Pending_At')->get()
            ->each(fn ($p) => $p->remaining = $this->pendingParts($p)->count());

        // Box milik pemilik yang masih dipegang operator lain (belum di-scan), bisa diambil kembali
        $held = Record_List::whereNull('Time_Record')->whereNotNull('Assigned_To')
            ->whereIn('Id_Record', Record::where('Id_User', $member->id)->select('Id_Record'))->get();
        $heldRecords = Record::whereIn('Id_Record', $held->pluck('Id_Record'))->pluck('Sequence_No_Record', 'Id_Record');
        $holders = DB::connection('rifa')->table('employees')->whereIn('id', $held->pluck('Assigned_To'))->pluck('nama', 'id');
        $heldBoxes = $held->groupBy(fn ($l) => $l->Id_Record . '|' . $l->Assigned_To . '|' . $l->Area . '|' . $l->Box)
            ->map(fn ($g) => [
                'record_id' => $g->first()->Id_Record, 'seq' => $heldRecords[$g->first()->Id_Record] ?? '-',
                'area' => $g->first()->Area, 'box' => $g->first()->Box, 'parts' => $g->count(),
                'nama' => $holders[$g->first()->Assigned_To] ?? '-',
            ])->values();

        return view('member.record.create', compact('remarkRecord', 'duplicateKanban', 'existingRecord', 'existingMember', 'matchedAreas', 'helpBoxes', 'existingRecordId', 'pendings', 'dupTakeovers', 'heldBoxes', 'helpNext'));
    }

    // Part pending milik pender: pemilik record = part tanpa Assigned_To; helper = part yang di-assign ke dirinya.
    private function pendingParts(RecordPending $p)
    {
        return Record_List::where('Id_Record', $p->Id_Record)->whereNull('Time_Record')
            ->when($p->record->Id_User == $p->Id_User, fn ($q) => $q->whereNull('Assigned_To'), fn ($q) => $q->where('Assigned_To', $p->Id_User));
    }

    // Pending aktif member lain di area yang terdaftar untuk member ini (dan masih punya sisa part)
    private function takeablePendings($member, $recordId)
    {
        if (!config('app.pending_enabled')) {
            return [];
        }
        $pendings = RecordPending::with('record')->where('Id_User', '!=', $member->id)->whereNull('Resumed_At')
            ->where('Id_Record', $recordId)
            ->whereHas('record', fn ($q) => $q->whereIn('Area', $member->registeredAreas()->toArray()))
            ->orderBy('Pending_At')->get();
        $names = DB::connection('rifa')->table('employees')->whereIn('id', $pendings->pluck('Id_User'))->pluck('nama', 'id');

        return $pendings->map(function ($p) use ($names) {
            $parts = $this->pendingParts($p)->orderBy('Sequence_No')->get();

            return [
                'id' => $p->id, 'seq' => $p->record->Sequence_No_Record, 'type' => $p->record->Type, 'area' => $p->record->Area,
                'nama' => $names[$p->Id_User] ?? '-', 'reason' => $p->Reason, 'since' => $p->Pending_At->format('d/m H:i'),
                'remaining' => $parts->count(),
                'boxes' => $parts->groupBy(fn ($l) => $l->Area . '|' . $l->Box)
                    ->map(fn ($g) => ['area' => $g->first()->Area, 'box' => $g->first()->Box, 'parts' => $g->count()])->values()->all(),
            ];
        })->filter(fn ($p) => $p['remaining'] > 0)->values()->all();
    }

    // Ambil alih pending operator lain: part belum di-scan di box terpilih (boxes[] = "area|box") pindah ke member ini.
    // Pending tetap aktif selama pender masih punya sisa part, agar sisanya bisa diambil member lain.
    public function takeover(Request $request, $pendingId)
    {
        if (!config('app.pending_enabled')) {
            return redirect()->route('member.record.create')->with('error', 'Fitur pending sedang dinonaktifkan.');
        }
        $request->validate([
            'boxes' => 'required|array|min:1',
            'boxes.*' => 'required|string',
        ]);

        $member = Auth::guard('member')->user();

        $record = DB::transaction(function () use ($request, $pendingId, $member) {
            $pending = RecordPending::lockForUpdate()->find($pendingId);
            $record = $pending ? Record::find($pending->Id_Record) : null;
            if (!$record || $pending->Resumed_At || $pending->Id_User == $member->id
                || !in_array($record->Area, $member->registeredAreas()->toArray())) {
                return null;
            }

            $chosen = array_unique($request->boxes);
            $ids = $this->pendingParts($pending)->lockForUpdate()->get()
                ->filter(fn ($l) => in_array($l->Area . '|' . $l->Box, $chosen))->pluck('Id_Record_List');
            if ($ids->isEmpty()) {
                return null;
            }

            // Pemilik yang mengambil kembali = NULL (milik pemilik); selain itu Assigned_To = pengambil
            Record_List::whereIn('Id_Record_List', $ids)->update(['Assigned_To' => $record->Id_User == $member->id ? null : $member->id]);
            if (!$this->pendingParts($pending)->exists()) {
                $pending->update(['Resumed_At' => now(), 'Taken_By' => $member->id]);
            }

            return $record;
        });

        $next = $record ? $this->nextPart($record->Id_Record, $member->id, $record->Id_User == $member->id) : null;
        if (!$next) {
            return redirect()->route('member.record.create')->with('error', 'Pending tidak bisa diambil alih (sudah dilanjutkan atau tidak ada part tersisa).');
        }

        return redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
            ->with('success', 'Anda mengambil alih kanban ini. Silahkan scan part.');
    }

    // Pemilik mengambil kembali box yang dipegang operator lain (mis. helper pergi tanpa pending).
    public function reclaim(Request $request, $recordId)
    {
        $request->validate(['box' => 'required|string']);

        $member = Auth::guard('member')->user();
        $record = Record::find($recordId);
        if (!$record || $record->Id_User != $member->id) {
            return redirect()->route('member.record.create')->with('error', 'Hanya pemilik kanban yang bisa mengambil kembali box.');
        }

        [$area, $box] = array_pad(explode('|', $request->box, 2), 2, null);
        $parts = Record_List::where('Id_Record', $recordId)->where('Area', $area)->where('Box', $box)
            ->whereNull('Time_Record')->whereNotNull('Assigned_To');
        $holders = (clone $parts)->distinct()->pluck('Assigned_To');
        $parts->update(['Assigned_To' => null]);
        RecordPending::where('Id_Record', $recordId)->whereIn('Id_User', $holders)->whereNull('Resumed_At')
            ->update(['Resumed_At' => now(), 'Taken_By' => $member->id]);

        $next = $this->nextPart($recordId, $member->id, true);

        return $next
            ? redirect()->route('member.record.scan-part', [$recordId, $next->Id_Record_List])->with('success', 'Box diambil kembali. Silahkan scan part.')
            : redirect()->route('member.record.create')->with('error', 'Tidak ada part yang bisa diambil kembali.');
    }

    // Pending kanban milik member ini (pemilik atau helper). Part yang belum di-scan tetap ada; lanjut lewat resume().
    public function pending(Request $request, $recordId)
    {
        if (!config('app.pending_enabled')) {
            return back()->with('error', 'Fitur pending sedang dinonaktifkan.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        $member = Auth::guard('member')->user();
        $record = Record::find($recordId);
        if (!$record || !$this->nextPart($record->Id_Record, $member->id, $record->Id_User == $member->id)) {
            return redirect()->route('member.record.create')->with('error', 'Tidak ada part yang bisa di-pending.');
        }

        RecordPending::firstOrCreate(
            ['Id_Record' => $record->Id_Record, 'Id_User' => $member->id, 'Resumed_At' => null],
            ['Reason' => $request->reason, 'Pending_At' => now()]
        );

        return redirect()->route('member.record.create')->with('success', "Kanban {$record->Sequence_No_Record} di-pending.");
    }

    public function resume($recordId)
    {
        $member = Auth::guard('member')->user();
        $record = Record::find($recordId);
        RecordPending::where('Id_Record', $recordId)->where('Id_User', $member->id)->whereNull('Resumed_At')->update(['Resumed_At' => now()]);

        $next = $record ? $this->nextPart($record->Id_Record, $member->id, $record->Id_User == $member->id) : null;

        return $next
            ? redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
            : redirect()->route('member.record.create');
    }
    public function myAreas(Request $request)
    {
        $member = Auth::guard('member')->user();
        return response()->json($member->registeredAreas());
    }

    public function getAreasByType(Request $request)
    {
        $typeName = $request->query('type');
        if (!$typeName) {
            return response()->json([]);
        }

        $type = Type::findByRawType($typeName);
        if (!$type) {
            return response()->json([]);
        }

        $areas = Marshalling::where('Id_Type', $type->Id_Type)
            ->where('Is_Active', 1)
            ->distinct()
            ->orderBy('Area')
            ->pluck('Area')
            ->map(function ($area) {
                foreach (array_keys(Marshalling::SUB_AREAS) as $parent) {
                    if (in_array($area, Marshalling::subAreasOf($parent))) {
                        return $parent;
                    }
                }
                return $area;
            })
            ->unique()
            ->values();

        return response()->json($areas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required',
            'production_date' => 'required',
            'type' => 'required',
            'area' => 'required',
        ]);

        $member = Auth::guard('member')->user();

        $existingRecord = Record::where('Sequence_No_Record', $request->sequence_no)
            ->where('Production_Date_Record', $request->production_date)
            ->where('Area', $request->area)
            ->first();

        if ($existingRecord) {
            $existingMember = $existingRecord->member;
            $memberAreas = $member->registeredAreas()->toArray();
            $existingMemberAreas = $existingMember ? $existingMember->registeredAreas()->toArray() : [];
            $matchedAreas = array_values(array_intersect($memberAreas, $existingMemberAreas));

            // Member lain yang terdaftar di area ini boleh membantu scan box yang belum disentuh
            $helpBoxes = [];
            if ($existingRecord->Id_User != $member->id && in_array($existingRecord->Area, $memberAreas)) {
                $helpBoxes = $this->availableHelpBoxes($existingRecord)->all();
            }

            return redirect()->back()
                ->with('duplicate_kanban', $request->sequence_no)
                ->with('help_boxes', $helpBoxes)
                ->with('takeovers', $this->takeablePendings($member, $existingRecord->Id_Record))
                ->with('existing_record_id', $existingRecord->Id_Record)
                ->with('existing_record', [
                    'sequence_no' => $existingRecord->Sequence_No_Record,
                    'production_date' => $existingRecord->Production_Date_Record,
                    'type' => $existingRecord->Type,
                    'area' => $existingRecord->Area,
                    'time_record' => $existingRecord->Time_Record,
                ])
                ->with('existing_member', $existingMember ? [
                    'nama' => $existingMember->nama,
                    'nik' => $existingMember->nik,
                ] : null)
                ->with('matched_areas', $matchedAreas);
        }

        $rawType = trim($request->type);
        $type = Type::findByRawType($rawType);

        if (!$type) {
            return redirect()->back()->with('error', 'Type "' . $request->type . '" tidak ditemukan di master data.');
        }

        $record = Record::create([
            'Id_User' => $member->id,
            'Sequence_No_Record' => $request->sequence_no,
            'Production_Date_Record' => $request->production_date,
            'Type' => $rawType,
            'Area' => $request->area,
            'Time_Record' => now(),
        ]);

        // Urutan scan diputuskan sekali di sini (default global / pengecualian per No. Sequence produksi)
        $mode = MarshallingSetting::scanOrderFor($request->sequence_no);
        $record->update(['Scan_Order' => $mode]);
        [$first, $second] = $mode === 'box' ? ['Box', 'Sequence_No'] : ['Sequence_No', 'Box'];

        $subAreas = Marshalling::subAreasOf($request->area);
        $query = Marshalling::whereIn('Area', $subAreas)
            ->where('Id_Type', $type->Id_Type)
            ->where('Is_Active', 1);
        if (count($subAreas) > 1) {
            $query->orderByRaw("FIELD(Area, '" . implode("', '", $subAreas) . "')");
        }
        $marshallings = $query->orderBy($first)->orderBy($second)->get();

        if ($marshallings->isEmpty()) {
            $record->delete();
            return redirect()->back()->with('error', 'No marshalling data found for this area and type.');
        }

        // Urutan scan = urutan Sequence_No di Record_List: per sub-area, lalu per box, lalu urutan rak
        $seqCounter = 1;
        $rows = [];
        foreach ($marshallings as $m) {
            $rows[] = [
                'Id_Record' => $record->Id_Record,
                'Id_Marshalling' => $m->Id_Marshalling,
                'Sequence_No' => $seqCounter++,
                'Code_Part' => $m->Code_Part,
                'Name_Part' => $m->Name_Part,
                'Code_Rack' => $m->Code_Rack,
                'Difference' => $m->Difference,
                'Location_Rack' => $m->Location_Rack,
                'Box' => $m->Box,
                'Qty' => $m->Qty,
                'Mode' => $m->Mode,
                'Area' => $m->Area,
                'Assigned_To' => null,
            ];
        }
        Record_List::insert($rows);

        $firstRecordList = $this->nextPart($record->Id_Record, $member->id, true);

        return redirect()->route('member.record.scan-part', [$record->Id_Record, $firstRecordList->Id_Record_List])
            ->with('success', 'Record created. Start recording parts.');
    }

    private function autoResume($recordId, $memberId)
    {
        RecordPending::where('Id_Record', $recordId)->where('Id_User', $memberId)->whereNull('Resumed_At')->update(['Resumed_At' => now()]);
    }

    // Part berikutnya yang belum di-scan, mengikuti Record_List.Sequence_No yang sudah ditetapkan di store().
    // Pemilik hanya melihat part yang belum di-assign; helper hanya part yang di-assign ke dirinya.
    private function nextPart($recordId, $memberId, $isOwner)
    {
        return Record_List::where('Id_Record', $recordId)
            ->whereNull('Time_Record')
            ->when($isOwner, fn ($q) => $q->whereNull('Assigned_To'), fn ($q) => $q->where('Assigned_To', $memberId))
            ->orderBy('Sequence_No')
            ->first();
    }

    // Box = pasangan (Area, Box). Tersedia jika belum ada part yang di-scan/di-assign dan bukan box yang sedang dikerjakan pemilik.
    private function availableHelpBoxes(Record $record)
    {
        $lists = Record_List::where('Id_Record', $record->Id_Record)->orderBy('Sequence_No')->get();
        $current = $lists->first(fn ($l) => $l->Time_Record === null && $l->Assigned_To === null);
        $currentKey = $current ? $current->Area . '|' . $current->Box : null;

        return $lists->groupBy(fn ($l) => $l->Area . '|' . $l->Box)
            ->filter(fn ($g, $key) => $key !== $currentKey
                && $g->every(fn ($l) => $l->Time_Record === null && $l->Assigned_To === null))
            ->map(fn ($g) => ['area' => $g->first()->Area, 'box' => $g->first()->Box, 'parts' => $g->count()])
            ->values();
    }

    public function help(Request $request, $recordId)
    {
        $request->validate(['box' => 'required|string']);

        $member = Auth::guard('member')->user();
        $record = Record::find($recordId);
        if (!$record || $record->Id_User == $member->id || !in_array($record->Area, $member->registeredAreas()->toArray())) {
            return redirect()->route('member.record.create')
                ->with('error', 'Anda tidak dapat membantu scan kanban ini.');
        }

        $assigned = DB::transaction(function () use ($request, $record, $member) {
            // Kunci semua baris record agar dua helper tidak mengambil box yang sama
            Record_List::where('Id_Record', $record->Id_Record)->lockForUpdate()->get();
            // Cek ulang setelah dikunci: box bisa sudah mulai di-scan pemilik sejak modal dibuka
            $available = $this->availableHelpBoxes($record)->map(fn ($b) => $b['area'] . '|' . $b['box'])->all();
            if (!in_array($request->box, $available, true)) {
                return 0;
            }
            [$area, $box] = array_pad(explode('|', $request->box, 2), 2, null);

            return Record_List::where('Id_Record', $record->Id_Record)
                ->where('Area', $area)
                ->where('Box', $box)
                ->whereNull('Time_Record')
                ->whereNull('Assigned_To')
                ->update(['Assigned_To' => $member->id]);
        });

        $first = $assigned ? $this->nextPart($record->Id_Record, $member->id, false) : null;
        if (!$first) {
            return redirect()->route('member.record.create')
                ->with('error', 'Box yang dipilih sudah diambil atau sedang dikerjakan operator lain.');
        }

        return redirect()->route('member.record.scan-part', [$record->Id_Record, $first->Id_Record_List])
            ->with('success', 'Anda membantu scan kanban ini. Silahkan scan part.');
    }

    // Pemilik hanya boleh scan part tanpa Assigned_To; helper hanya part yang di-assign ke dirinya.
    private function canScan($record, $recordList, $member)
    {
        return $recordList->Assigned_To === null
            ? $record->Id_User == $member->id
            : $recordList->Assigned_To == $member->id;
    }

    // Setelah bagian member selesai: tunggu operator lain, atau tutup record jika semua part sudah ter-scan.
    private function finishRecord($record, $member)
    {
        $isOwner = $record->Id_User == $member->id;

        if (Record_List::where('Id_Record', $record->Id_Record)->whereNull('Time_Record')->exists()) {
            // Helper selesai satu box: tawarkan box bebas berikutnya
            $next = $isOwner ? collect() : $this->availableHelpBoxes($record);
            if ($next->isNotEmpty()) {
                return redirect()->route('member.record.create')
                    ->with('success', 'Box Anda selesai.')
                    ->with('help_next', ['record_id' => $record->Id_Record, 'seq' => $record->Sequence_No_Record, 'boxes' => $next->all()]);
            }

            // Pemilik tetap dapat remark walau record belum selesai seluruhnya
            return redirect()->route('member.record.create')
                ->with($isOwner
                    ? ['success' => 'Bagian Anda selesai. Menunggu operator lain menyelesaikan box sisanya.', 'remark_record_id' => $record->Id_Record]
                    : ['success' => 'Bagian Anda selesai. Menunggu operator lain menyelesaikan box sisanya.']);
        }

        // Trigger iseki_efficiency for marshalling area scan when last part is done
        $this->notifyEfficiency($record, $member->id ?? null);

        return redirect()->route('member.record.create')
            // Pemilik selalu; yang men-scan part terakhir hanya jika belum ada remark (mis. part pemilik diambil alih)
            ->with($isOwner || !$record->Remark
                ? ['success' => 'All parts recorded successfully!', 'remark_record_id' => $record->Id_Record]
                : ['success' => 'All parts recorded successfully!']);
    }

    public function recordPart($id)
    {
        return redirect()->route('member.record.create');
    }

    public function scanPart($recordId, $recordListId)
    {
        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->find($recordId);

        $member = Auth::guard('member')->user();
        $recordList = $record ? $record->recordLists->firstWhere('Id_Record_List', $recordListId) : null;
        if (!$record || ($recordList ? !$this->canScan($record, $recordList, $member) : $record->Id_User != $member->id)) {
            return redirect()->route('member.record.create')
                ->with('error', 'Record tidak ditemukan atau telah dihapus. Silahkan scan QR kanban baru.');
        }

        if (!$recordList) {
            return redirect()->route('member.record.create')
                ->with('error', 'List part tidak ditemukan atau telah dihapus. Silahkan scan QR kanban baru.');
        }

        if ($recordList->Time_Record !== null) {
            return redirect()->route('member.record.create')
                ->with('error', 'This part has already been recorded.');
        }

        // Membuka scan = otomatis lanjut dari pending
        $this->autoResume($record->Id_Record, $member->id);

        // Mode box: awal box = member ini belum men-scan part apa pun di Area+Box yang sama
        $isBoxStart = false;
        $boxLabel = '';
        $boxParts = 0;
        if ($record->Scan_Order === 'box') {
            $isOwner = $record->Id_User == $member->id;
            $mine = Record_List::where('Id_Record', $record->Id_Record)
                ->where('Area', $recordList->Area)->where('Box', $recordList->Box)
                ->when($isOwner, fn ($q) => $q->whereNull('Assigned_To'), fn ($q) => $q->where('Assigned_To', $member->id));
            $isBoxStart = !(clone $mine)->whereNotNull('Time_Record')->exists();
            $boxParts = (clone $mine)->count();
            $boxLabel = (preg_match('/^(transmisi|sub_engine)_/', $recordList->Area)
                ? ucwords(str_replace('_', ' ', $recordList->Area)) . ' - ' : '') . 'Box ' . $recordList->Box;
        }

        return view('member.record.record-scan', compact('record', 'recordList', 'isBoxStart', 'boxLabel', 'boxParts'))
            ->with('isPunished', $member->isPunished())
            ->with('scanOrder', $record->Scan_Order ?? 'sequence');
    }

    public function updatePart(Request $request, $recordListId)
    {
        $recordList = Record_List::find($recordListId);
        if (!$recordList) {
            return redirect()->route('member.record.create')
                ->with('error', 'Record part tidak ditemukan atau telah dihapus oleh Admin. Silahkan scan QR kanban baru.');
        }

        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->find($recordList->Id_Record);

        $member = Auth::guard('member')->user();
        if (!$record || !$this->canScan($record, $recordList, $member)) {
            return redirect()->route('member.record.create')
                ->with('error', 'Record tidak ditemukan atau telah dihapus oleh Admin. Silahkan scan QR kanban baru.');
        }

        $isOwner = $record->Id_User == $member->id;
        $this->autoResume($record->Id_Record, $member->id);

        // Sudah pernah di-scan (double submit): jangan timpa, lanjut ke part berikutnya
        if ($recordList->Time_Record !== null) {
            $next = $this->nextPart($record->Id_Record, $member->id, $isOwner);
            return $next
                ? redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
                : redirect()->route('member.record.create');
        }

        $isEmpty = $request->boolean('Is_Empty');
        $isPunished = $member->isPunished();

        $rules = ['Qty_Record' => 'nullable|integer|min:0'];
        if (!$isEmpty) {
            $rules['Code_Rack'] = 'required';
            if ($isPunished) {
                $rules['Qty_Record'] = 'required|integer|min:0';
            }
        }
        $request->validate($rules);

        if (!$isEmpty && strtoupper($request->Code_Rack) !== strtoupper($recordList->Code_Rack)) {
            return redirect()->back()->with('error', 'Code Rack does not match! Expected: ' . $recordList->Code_Rack);
        }

        $updateData = [
            'Time_Record' => now(),
            'Is_Empty' => $isEmpty ? 1 : null,
        ];

        if ($isEmpty) {
            $updateData['Qty_Record'] = 0;
        } else {
            $updateData['Qty_Record'] = $isPunished ? $request->Qty_Record : $recordList->Qty;
        }

        if ($isEmpty) {
            $recordList->update($updateData);

            // Cek apakah part atau rak ini sudah pernah dilaporkan kosong hari ini oleh member marshalling mana pun
            $alreadyReportedToday = Record_List::where('Id_Record_List', '!=', $recordList->Id_Record_List)
                ->where('Is_Empty', 1)
                ->whereDate('Time_Record', Carbon::today())
                ->where(function ($q) use ($recordList) {
                    $q->where('Code_Part', $recordList->Code_Part)
                      ->orWhere('Code_Rack', $recordList->Code_Rack);
                })
                ->exists();

            if (!$alreadyReportedToday) {
                $emptyPayload = [
                    'code_rack' => $recordList->Code_Rack,
                    'id_member' => $member->id,
                    'sequence_no_record' => $record->Sequence_No_Record,
                ];
                dispatch(function () use ($emptyPayload) {
                    try {
                        Http::connectTimeout(2)->timeout(3)->post('http://192.168.173.201/iseki_scan/api/marshalling-empty', $emptyPayload);
                    } catch (\Exception $e) {
                        // fire-and-forget, ignore errors
                    }
                })->afterResponse();
            }

            $next = $this->nextPart($record->Id_Record, $member->id, $isOwner);

            if ($next) {
                $redirect = redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
                    ->with('success', 'Part recorded! Proceed to next part.');

                $isTransmisiTransition = ($recordList->Area !== $next->Area &&
                    in_array($recordList->Area, ['transmisi_a', 'transmisi_b']) &&
                    in_array($next->Area, ['transmisi_b', 'transmisi_c']));

                $isSubEngineTransition = ($recordList->Area === 'sub_engine_a' && $next->Area === 'sub_engine_b');

                if (($isTransmisiTransition || $isSubEngineTransition) && $record->Scan_Order !== 'box') {
                    $redirect->with('box_transition', true);
                }

                return $redirect;
            }

            return $this->finishRecord($record, $member);
        }

        if ($recordList->Mode === 'ai' && $request->filled('image_data')) {
            $qtyMatch = (int)$request->Qty_Record === (int)$recordList->Qty;
            if (!$qtyMatch) {
                $imageData = $request->input('image_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                    $base64 = substr($imageData, strpos($imageData, ',') + 1);
                    $decoded = base64_decode($base64);
                    if ($decoded === false) {
                        $decoded = null;
                    }
                }

                if (!empty($decoded)) {
                    $folder = 'uploads/ng/' . now()->format('mY');
                    $publicPath = public_path($folder);
                    if (!is_dir($publicPath)) {
                        mkdir($publicPath, 0755, true);
                    }

                    $filename = 'ng_' . $recordList->Id_Record_List . '_' . now()->format('YmdHis') . '.' . $ext;
                    $filepath = $publicPath . '/' . $filename;

                    $img = imagecreatefromstring($decoded);
                    if ($img) {
                        $quality = 90;
                        ob_start();
                        imagejpeg($img, null, $quality);
                        ob_end_clean();

                        $bytes = strlen($decoded);
                        if ($bytes > 500000) {
                            $quality = (int)(90 * (500000 / $bytes));
                            $quality = max(10, min(90, $quality));
                        }

                        if ($ext === 'jpg' || $ext === 'jpeg') {
                            imagejpeg($img, $filepath, $quality);
                        } elseif ($ext === 'png') {
                            $pngQuality = (int)(9 - ($quality / 10));
                            imagepng($img, $filepath, max(0, min(9, $pngQuality)));
                        } else {
                            imagejpeg($img, $filepath, $quality);
                        }
                        imagedestroy($img);

                        $filesize = filesize($filepath);
                        if ($filesize > 500000 && file_exists($filepath)) {
                            $jpgData = file_get_contents($filepath);
                            $img2 = imagecreatefromstring($jpgData);
                            if ($img2) {
                                $quality = (int)(90 * (500000 / $filesize));
                                $quality = max(5, min(85, $quality));
                                imagejpeg($img2, $filepath, $quality);
                                imagedestroy($img2);
                            }
                        }

                        $updateData['Image_Ng'] = $folder . '/' . $filename;
                    }
                }
            }
        }

        $recordList->update($updateData);

        $next = $this->nextPart($record->Id_Record, $member->id, $isOwner);

        if ($next) {
            $redirect = redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
                ->with('success', 'Part recorded! Proceed to next part.');

            // Cek apakah ada pergantian sub-area (transmisi_a -> transmisi_b, transmisi_b -> transmisi_c, atau sub_engine_a -> sub_engine_b)
            $isTransmisiTransition = ($recordList->Area !== $next->Area &&
                in_array($recordList->Area, ['transmisi_a', 'transmisi_b']) &&
                in_array($next->Area, ['transmisi_b', 'transmisi_c']));

            $isSubEngineTransition = ($recordList->Area === 'sub_engine_a' && $next->Area === 'sub_engine_b');

            if (($isTransmisiTransition || $isSubEngineTransition) && $record->Scan_Order !== 'box') {
                $redirect->with('box_transition', true);
            }

            return $redirect;
        }

        return $this->finishRecord($record, $member);
    }

    // Kirim scan ke iseki_efficiency setelah response terkirim agar operator tidak menunggu server eksternal
    private function notifyEfficiency($record, $memberId)
    {
        $payload = [
            'area' => $record->Area,
            'type' => $record->Type,
            'sequence_no' => $record->Sequence_No_Record,
            'production_date' => $record->Production_Date_Record,
            'id_member' => $memberId,
        ];

        dispatch(function () use ($payload) {
            try {
                Http::connectTimeout(2)->timeout(3)->post('http://192.168.173.201/iseki_efficiency/public/api/marshalling-scan', $payload);
            } catch (\Exception $e) {
                \Log::error('Gagal mengirim scan marshalling ke iseki_efficiency: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    public function saveRemark(Request $request, $recordId)
    {
        $record = Record::find($recordId);
        $member = Auth::guard('member')->user();
        if (!$record || ($record->Id_User != $member->id
            && !Record_List::where('Id_Record', $recordId)->where('Assigned_To', $member->id)->exists())) {
            return redirect()->route('member.record.create')
                ->with('error', 'Record tidak ditemukan atau telah dihapus.');
        }

        $request->validate([
            'Remark' => 'nullable|string|max:5000',
        ]);

        // Remark orang lain tidak ditimpa, digabung dengan " | "
        $record->update(['Remark' => implode(' | ', array_filter([trim($record->Remark), trim($request->Remark)])) ?: null]);

        return redirect()->route('member.record.create')
            ->with('success', 'Record completed successfully!');
    }

    public function activePartKurangNotifications(Request $request)
    {
        $member = Auth::guard('member')->user();
        if (!$member) {
            return response()->json(['notifications' => []]);
        }

        $items = \App\Models\PartKurang::where(function($q) use ($member) {
                $q->where('id_user', $member->id);
                if (!empty($member->nik)) {
                    $q->orWhere('member_nik', (string)$member->nik);
                }
            })
            ->where('status', 'pending')
            ->whereNull('dismissed_at')
            ->orderBy('comment_time', 'desc')
            ->get();

        $reporterNiks = $items->pluck('perakitan_nik')->filter()->unique();
        $employees = DB::connection('rifa')->table('employees')
            ->whereIn('nik', $reporterNiks)
            ->pluck('nama', 'nik');

        $notifications = $items->map(function ($item) use ($employees) {
            $reporterName = '-';
            if ($item->perakitan_nik) {
                $reporterName = $employees[$item->perakitan_nik] ?? $item->perakitan_nik;
            }

            return [
                'id'            => $item->id,
                'reporter_name' => $reporterName,
                'comment'       => $item->comment,
                'category'      => $item->category ?? 'kurang',
                'comment_time'  => $item->comment_time ? $item->comment_time->format('d/m/Y H:i') : '-',
                'sequence_no'   => $item->sequence_no ?? '-',
                'area'          => ucwords(str_replace('_', ' ', $item->area ?? '-')),
            ];
        });

        return response()->json([
            'count'         => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    public function dismissPartKurangNotification($id)
    {
        $member = Auth::guard('member')->user();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $partKurang = \App\Models\PartKurang::where('id', $id)
            ->where(function($q) use ($member) {
                $q->where('id_user', $member->id);
                if (!empty($member->nik)) {
                    $q->orWhere('member_nik', (string)$member->nik);
                }
            })
            ->first();

        if ($partKurang) {
            $partKurang->update([
                'dismissed_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
