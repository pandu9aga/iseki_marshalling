<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();
$spreadsheet = $reader->load('C:/Users/MCC2104001_003/Downloads/MF1GC - PICKING CARD (MARSHALING).xlsx');

foreach ($spreadsheet->getAllSheets() as $sheet) {
    echo 'Sheet: ' . $sheet->getTitle() . "\n";
}

echo "\n";
$sheet = $spreadsheet->getSheet(0);

echo "Row 8 (header row):\n";
$row8 = $sheet->rangeToArray('A8:Z8')[0];
foreach ($row8 as $i => $v) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    echo "  $col (index $i): " . ($v ?? 'NULL') . "\n";
}

echo "\nRow 9 (first data row):\n";
$row9 = $sheet->rangeToArray('A9:Z9')[0];
foreach ($row9 as $i => $v) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    echo "  $col (index $i): " . ($v ?? 'NULL') . "\n";
}

echo "\nRow 10 (second data row):\n";
$row10 = $sheet->rangeToArray('A10:Z10')[0];
foreach ($row10 as $i => $v) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    echo "  $col (index $i): " . ($v ?? 'NULL') . "\n";
}
