<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\Marshalling;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $member = Auth::guard('member')->user();
        if ($request->ajax()) {
            $data = Record::with('recordLists')
                ->where('Id_User', $member->id)
                ->orderBy('Time_Record', 'desc');
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $total = $row->recordLists->count();
                    $completed = $row->recordLists->whereNotNull('Time_Record')->count();
                    return "$completed / $total";
                })
                ->editColumn('Area', function($record) {
                    return $record->Area ? ucwords(str_replace('_', ' ', $record->Area)) : '-';
                })
                ->setRowId(function ($row) {
                    return $row->Id_Record;
                })
                ->make(true);
        }
        return redirect()->route('member.record.create');
    }

    public function create()
    {
        $member = Auth::guard('member')->user();

        $incompleteRecord = Record::where('Id_User', $member->id)
            ->whereHas('recordLists', function ($q) {
                $q->whereNull('Time_Record');
            })
            ->orderBy('Time_Record', 'desc')
            ->first();

        if ($incompleteRecord) {
            $nextList = Record_List::where('Id_Record', $incompleteRecord->Id_Record)
                ->whereNull('Time_Record')
                ->orderBy('Sequence_No')
                ->first();

            if ($nextList) {
                return redirect()->route('member.record.scan-part', [
                    $incompleteRecord->Id_Record,
                    $nextList->Id_Record_List
                ]);
            }
        }

        return view('member.record.create');
    }

    public function getAreasByType(Request $request)
    {
        $typeName = $request->query('type');
        if (!$typeName) {
            return response()->json([]);
        }

        $type = Type::where('Type', $typeName)->first();
        if (!$type) {
            return response()->json([]);
        }

        $areas = Marshalling::where('Id_Type', $type->Id_Type)
            ->distinct()
            ->orderBy('Area')
            ->pluck('Area');

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

        $record = Record::create([
            'Id_User' => $member->id,
            'Sequence_No_Record' => $request->sequence_no,
            'Production_Date_Record' => $request->production_date,
            'Type' => $request->type,
            'Area' => $request->area,
            'Time_Record' => now(),
        ]);

        $type = Type::where('Type', trim($request->type))->first();

        if (!$type) {
            $record->delete();
            return redirect()->back()->with('error', 'Type "' . $request->type . '" tidak ditemukan di master data.');
        }

        $marshallings = Marshalling::where('Area', $request->area)
            ->where('Id_Type', $type->Id_Type)
            ->orderBy('Sequence_No')
            ->get();

        if ($marshallings->isEmpty()) {
            $record->delete();
            return redirect()->back()->with('error', 'No marshalling data found for this area and type.');
        }

        foreach ($marshallings as $m) {
            Record_List::create([
                'Id_Record' => $record->Id_Record,
                'Id_Marshalling' => $m->Id_Marshalling,
                'Sequence_No' => $m->Sequence_No,
                'Code_Part' => $m->Code_Part,
                'Name_Part' => $m->Name_Part,
                'Code_Rack' => $m->Code_Rack,
                'Difference' => $m->Difference,
                'Location_Rack' => $m->Location_Rack,
                'Box' => $m->Box,
                'Qty' => $m->Qty,
                'Mode' => $m->Mode,
                'Area' => $m->Area,
            ]);
        }

        $firstRecordList = Record_List::where('Id_Record', $record->Id_Record)
            ->orderBy('Sequence_No')
            ->first();

        return redirect()->route('member.record.scan-part', [$record->Id_Record, $firstRecordList->Id_Record_List])
            ->with('success', 'Record created. Start recording parts.');
    }

    public function recordPart($id)
    {
        return redirect()->route('member.record.create');
    }

    public function scanPart($recordId, $recordListId)
    {
        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->findOrFail($recordId);

        $member = Auth::guard('member')->user();
        if ($record->Id_User != $member->id) {
            abort(403);
        }

        $recordList = $record->recordLists->firstWhere('Id_Record_List', $recordListId);
        if (!$recordList) {
            abort(404);
        }

        if ($recordList->Time_Record !== null) {
            return redirect()->route('member.record.create')
                ->with('error', 'This part has already been recorded.');
        }

        return view('member.record.record-scan', compact('record', 'recordList'));
    }

    public function updatePart(Request $request, $recordListId)
    {
        $recordList = Record_List::findOrFail($recordListId);
        $record = Record::with(['recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])->findOrFail($recordList->Id_Record);

        $member = Auth::guard('member')->user();
        if ($record->Id_User != $member->id) {
            abort(403);
        }

        $isEmpty = $request->boolean('Is_Empty');

        $request->validate([
            'Code_Rack' => 'required',
            'Qty_Record' => $isEmpty ? 'nullable|integer|min:0' : 'required|integer|min:0',
        ]);

        if ($request->Code_Rack !== $recordList->Code_Rack) {
            return redirect()->back()->with('error', 'Code Rack does not match! Expected: ' . $recordList->Code_Rack);
        }

        $updateData = [
            'Time_Record' => now(),
            'Is_Empty' => $isEmpty ? 1 : null,
        ];

        if ($isEmpty) {
            $updateData['Qty_Record'] = 0;
        } else {
            $updateData['Qty_Record'] = $request->Qty_Record;
        }

        if ($isEmpty) {
            $recordList->update($updateData);

            try {
                Http::timeout(10)->post('http://192.168.173.201/iseki_scan/api/marshalling-empty', [
                    'code_rack' => $recordList->Code_Rack,
                ]);
            } catch (\Exception $e) {
                // fire-and-forget, ignore errors
            }

            $next = Record_List::where('Id_Record', $record->Id_Record)
                ->whereNull('Time_Record')
                ->orderBy('Sequence_No')
                ->first();

            if ($next) {
                return redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
                    ->with('success', 'Part recorded! Proceed to next part.');
            }

            return redirect()->route('member.record.create')
                ->with('success', 'All parts recorded successfully!');
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

        $next = Record_List::where('Id_Record', $record->Id_Record)
            ->whereNull('Time_Record')
            ->orderBy('Sequence_No')
            ->first();

        if ($next) {
            return redirect()->route('member.record.scan-part', [$record->Id_Record, $next->Id_Record_List])
                ->with('success', 'Part recorded! Proceed to next part.');
        }

        return redirect()->route('member.record.create')
            ->with('success', 'All parts recorded successfully!');
    }
}
