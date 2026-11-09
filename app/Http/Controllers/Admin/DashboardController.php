<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Record;
use App\Models\Record_List;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?: now()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $query = Record::with(['recordLists', 'member', 'pendings']);

        if ($date == $today) {
            $query->where(function ($q) use ($date) {
                $q->whereDate('Time_Record', $date)
                  ->orWhereHas('recordLists', function ($q2) {
                      $q2->whereNull('Time_Record');
                  });
            });
        } else {
            $query->whereDate('Time_Record', $date);
        }

        $allRecords = $query->get();

        // Calculate duration for each record
        $allRecords->each(function ($record) {
            $record->computed_duration = $record->calculateDurationSeconds();
        });

        // Part di-scan per area+member, dan kanban yang dibantu per area+helper
        $partsByMember = [];
        $helped = [];
        foreach ($allRecords as $r) {
            foreach ($r->scannersSummary() as $s) {
                $partsByMember[$r->Area][$s['id']] = ($partsByMember[$r->Area][$s['id']] ?? 0) + $s['parts'];
                if ($s['role'] === 'Helper') {
                    $helped[$r->Area][$s['id']][] = $s + ['record' => $r];
                }
            }
        }

        $personIds = $allRecords->pluck('Id_User')->merge(collect($helped)->flatMap(fn($h) => array_keys($h)))->unique();
        $totalMembers = $personIds->count();
        $totalRecords = $allRecords->count();
        $totalDone = $allRecords->filter(fn($r) => $r->recordLists->every(fn($rl) => $rl->Time_Record !== null))->count();
        $totalProgress = $totalRecords - $totalDone;

        $totalNg = Record_List::where('Mode', 'ai')
            ->whereNotNull('Time_Record')
            ->whereColumn('Qty_Record', '!=', 'Qty')
            ->where(function ($q) {
                $q->whereNull('Status_Ng')
                  ->orWhere('Status_Ng', '!=', 'ng_ok');
            })
            ->whereHas('record', function ($q) use ($date) {
                $q->whereDate('Time_Record', $date);
            })
            ->count();

        $users = $personIds->isEmpty() ? collect() : Member::whereIn('id', $personIds->all())->get()->keyBy('id');
        $names = $users->pluck('nama', 'id');

        // Group by Area -> User -> Type (helper tanpa record sendiri di area itu dapat grup kosong)
        $records = $allRecords->groupBy('Area')->sortKeys()->map(function ($areaRecords, $area) use ($users, $helped) {
            $groups = $areaRecords->groupBy('Id_User')->map(fn($userRecords) => $userRecords->groupBy('Type'));
            foreach (array_keys($helped[$area] ?? []) as $hid) {
                if (!$groups->has($hid)) {
                    $groups[$hid] = collect();
                }
            }
            return $groups->sortBy(fn($typeGroups, $userId) => $users->has($userId) ? strtolower($users[$userId]->nama) : 'unknown');
        });

        $chartData = [];
        foreach ($records as $area => $areaGroups) {
            foreach ($areaGroups as $userId => $typeGroups) {
                $memberName = $users->has($userId) ? $users[$userId]->nama : 'Unknown';
                $doneCount = 0;
                foreach ($typeGroups as $type => $typeRecords) {
                    $doneCount += $typeRecords->filter(fn($r) => $r->recordLists->every(fn($rl) => $rl->Time_Record !== null))->count();
                }
                
                $areaLabel = ucwords(str_replace('_', ' ', $area));
                $chartData[] = ['member' => $areaLabel . ' - ' . $memberName, 'done' => $doneCount,
                    'helper' => collect($helped[$area][$userId] ?? [])->sum('parts')];
            }
        }

        return view('admin.dashboard', compact(
            'records', 'date', 'totalMembers', 'totalRecords',
            'totalDone', 'totalProgress', 'totalNg', 'chartData', 'partsByMember', 'helped', 'names'
        ));
    }

    public function export(Request $request)
    {
        $date = $request->date ?: now()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $query = Record::with(['recordLists', 'member', 'pendings']);

        if ($date == $today) {
            $query->where(function ($q) use ($date) {
                $q->whereDate('Time_Record', $date)
                  ->orWhereHas('recordLists', function ($q2) {
                      $q2->whereNull('Time_Record');
                  });
            });
        } else {
            $query->whereDate('Time_Record', $date);
        }

        $records = $query->get()
            ->filter(fn($r) => $r->recordLists->every(fn($rl) => $rl->Time_Record !== null))
            ->groupBy('Id_User')
            ->map(function ($userRecords) {
                return $userRecords->groupBy('Type');
            })
            ->filter(fn($typeGroups) => $typeGroups->count() > 0);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($date);

        $sheet->setCellValue('A1', 'Marshalling: ' . $date);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->mergeCells('A1:D1');

        $sheet->setCellValue('A2', 'Member');
        $sheet->setCellValue('B2', 'Type');
        $sheet->setCellValue('C2', 'Record');
        $sheet->setCellValue('D2', 'Total');

        $row = 3;
        $grandTotal = 0;
        foreach ($records as $userId => $typeGroups) {
            $memberName = $typeGroups->first()->first()->member->nama ?? 'Unknown';
            $totalForMember = 0;
            $typeCount = 0;
            $typeRows = [];
            foreach ($typeGroups as $type => $typeRecords) {
                $cnt = $typeRecords->count();
                $totalForMember += $cnt;
                $typeCount++;
                $typeRows[] = ['type' => $type, 'count' => $cnt];
            }
            $grandTotal += $totalForMember;
            $startRow = $row;
            foreach ($typeRows as $tr) {
                $sheet->setCellValue('A' . $row, $memberName);
                $sheet->setCellValue('B' . $row, $tr['type']);
                $sheet->setCellValue('C' . $row, $tr['count']);
                $row++;
            }
            $endRow = $row - 1;
            if ($typeCount > 1) {
                $sheet->mergeCells("A{$startRow}:A{$endRow}");
            }
            $sheet->setCellValue('D' . $startRow, $totalForMember);
            if ($typeCount > 1) {
                $sheet->mergeCells("D{$startRow}:D{$endRow}");
            }
        }

        $totalRow = $row;
        $sheet->setCellValue('A' . $totalRow, 'Total Keseluruhan');
        $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
        $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
        $sheet->setCellValue('D' . $totalRow, $grandTotal);
        $sheet->getStyle('D' . $totalRow)->getFont()->setBold(true);
        $row++;

        $lastRow = $row - 1;
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
        ]);
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Marshalling_' . $date . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }
}
