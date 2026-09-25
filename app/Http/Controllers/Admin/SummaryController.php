<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Record;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?: now()->format('Y-m-d');

        $records = Record::with(['member', 'recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])
            ->whereDate('Production_Date_Record', $date)
            ->get();

        $typeMap = \App\Models\Type::with('mainType')->get()->keyBy('Type');
        $summaryData = [];

        // Group by Area
        $groupedByArea = $records->groupBy('Area');

        foreach ($groupedByArea as $area => $areaRecords) {
            $groupedByMainType = $areaRecords->groupBy(function ($record) use ($typeMap) {
                return $typeMap[$record->Type]->mainType->Main_Type ?? 'Belum Dikategorikan';
            });

            $mainTypesData = [];
            $areaTotalTractors = $areaRecords->count();
            $areaCompletedTractors = 0;
            $areaTotalDurationSeconds = 0;

            foreach ($groupedByMainType as $mainType => $mainTypeRecords) {
                $groupedByType = $mainTypeRecords->groupBy('Type');

                $typesData = [];
                $mtTotalTractors = $mainTypeRecords->count();
                $mtCompletedTractors = 0;
                $mtTotalDurationSeconds = 0;

                foreach ($groupedByType as $type => $typeRecords) {
                    $typeTotalTractors = $typeRecords->count();
                    $typeCompletedTractors = $typeRecords->filter(function ($r) {
                        return $r->recordLists->count() > 0 &&
                            $r->recordLists->every(fn($rl) => $rl->Time_Record !== null);
                    })->count();

                    $typeTotalDurationSeconds = $typeRecords->sum(function ($r) {
                        return $r->calculateDurationSeconds();
                    });

                    $mtCompletedTractors += $typeCompletedTractors;
                    $mtTotalDurationSeconds += $typeTotalDurationSeconds;
                    $areaCompletedTractors += $typeCompletedTractors;
                    $areaTotalDurationSeconds += $typeTotalDurationSeconds;

                    // Jangan di-group by Id_User, melainkan tampilkan setiap record traktor satu persatu
                    $membersDetails = [];
                    foreach ($typeRecords as $r) {
                        $memberName = $r->member->nama ?? 'Unknown';
                        $mTotal = 1;
                        $mCompleted = ($r->recordLists->count() > 0 &&
                            $r->recordLists->every(fn($rl) => $rl->Time_Record !== null)) ? 1 : 0;

                        $mDurationSeconds = $r->calculateDurationSeconds();

                        $membersDetails[] = [
                            'name' => $memberName,
                            'total' => $mTotal,
                            'completed' => $mCompleted,
                            'duration' => round($mDurationSeconds / 60) . ' Menit'
                        ];
                    }

                    // Total Jam di level Tipe Traktor ditampilkan sebagai RATA-RATA:
                    // total durasi tipe ini di area ini dibagi berapa kali tipe ini
                    // dilakukan marshalling di area ini (bukan total/sum keseluruhan).
                    $typeAvgDurationSeconds = $typeTotalTractors > 0
                        ? $typeTotalDurationSeconds / $typeTotalTractors
                        : 0;

                    $typesData[] = [
                        'type' => $type,
                        'total_tractors' => $typeTotalTractors,
                        'completed_tractors' => $typeCompletedTractors,
                        'duration' => round($typeAvgDurationSeconds / 60) . ' Menit',
                        'members' => collect($membersDetails)->sortByDesc('completed')->values()->all()
                    ];
                }

                $mainTypesData[] = [
                    'main_type' => $mainType,
                    'total_tractors' => $mtTotalTractors,
                    'completed_tractors' => $mtCompletedTractors,
                    'duration' => round($mtTotalDurationSeconds / 60) . ' Menit',
                    'types' => collect($typesData)->sortBy('type')->values()->all()
                ];
            }

            $summaryData[] = [
                'area' => ucwords(str_replace('_', ' ', $area)),
                'total_tractors' => $areaTotalTractors,
                'completed_tractors' => $areaCompletedTractors,
                'duration' => round($areaTotalDurationSeconds / 60) . ' Menit',
                'main_types' => collect($mainTypesData)->sortBy('main_type')->values()->all()
            ];
        }

        $summaryData = collect($summaryData)->sortBy('area')->values()->all();

        return view('admin.summary', compact('summaryData', 'date'));
    }

    public function export(Request $request)
    {
        $date = $request->date ?: now()->format('Y-m-d');

        $records = Record::with(['member', 'recordLists' => function ($q) {
            $q->orderBy('Sequence_No');
        }])
            ->whereDate('Production_Date_Record', $date)
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary ' . $date);

        // Styling headers
        $sheet->setCellValue('A1', 'Rangkuman Marshalling: ' . $date);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:F1');

        $headers = ['Area', 'Kategori (Main Type)', 'Tipe Traktor', 'Nama Member', 'Perolehan (Selesai/Total)', 'Total Jam Member'];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '3', $header);
            $sheet->getStyle($colIndex . '3')->getFont()->setBold(true);
            $sheet->getStyle($colIndex . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF2F2F2');
            $colIndex++;
        }

        $row = 4;
        $typeMap = \App\Models\Type::with('mainType')->get()->keyBy('Type');
        $groupedByArea = $records->groupBy('Area');

        foreach ($groupedByArea as $area => $areaRecords) {
            $areaName = ucwords(str_replace('_', ' ', $area));
            $startAreaRow = $row;

            $groupedByMainType = $areaRecords->groupBy(function ($record) use ($typeMap) {
                return $typeMap[$record->Type]->mainType->Main_Type ?? 'Belum Dikategorikan';
            });

            foreach ($groupedByMainType as $mainType => $mainTypeRecords) {
                $startMainTypeRow = $row;
                $groupedByType = $mainTypeRecords->groupBy('Type');

                foreach ($groupedByType as $type => $typeRecords) {
                    $startTypeRow = $row;
                    // Tampilkan per traktor, jangan di-group by Id_User
                    foreach ($typeRecords as $r) {
                        $memberName = $r->member->nama ?? 'Unknown';
                        $mTotal = 1;
                        $mCompleted = ($r->recordLists->count() > 0 &&
                            $r->recordLists->every(fn($rl) => $rl->Time_Record !== null)) ? 1 : 0;

                        $mDurationSeconds = $r->calculateDurationSeconds();
                        $durationText = round($mDurationSeconds / 60) . ' Menit';

                        $sheet->setCellValue('A' . $row, $areaName);
                        $sheet->setCellValue('B' . $row, $mainType);
                        $sheet->setCellValue('C' . $row, $type);
                        $sheet->setCellValue('D' . $row, $memberName);
                        $sheet->setCellValue('E' . $row, $mCompleted . ' / ' . $mTotal);
                        $sheet->setCellValue('F' . $row, $durationText);

                        $row++;
                    }

                    // Merge Type Column if there are multiple members for this type
                    $endTypeRow = $row - 1;
                    if ($endTypeRow > $startTypeRow) {
                        $sheet->mergeCells("C{$startTypeRow}:C{$endTypeRow}");
                        $sheet->getStyle("C{$startTypeRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                }

                // Merge Main Type Column
                $endMainTypeRow = $row - 1;
                if ($endMainTypeRow > $startMainTypeRow) {
                    $sheet->mergeCells("B{$startMainTypeRow}:B{$endMainTypeRow}");
                    $sheet->getStyle("B{$startMainTypeRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            // Merge Area Column if there are multiple types/members for this area
            $endAreaRow = $row - 1;
            if ($endAreaRow > $startAreaRow) {
                $sheet->mergeCells("A{$startAreaRow}:A{$endAreaRow}");
                $sheet->getStyle("A{$startAreaRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        }

        // Apply borders
        $lastRow = $row - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle("A3:F{$lastRow}")->getBorders()->applyFromArray([
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ]);
        }

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Rangkuman_Marshalling_' . $date . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
