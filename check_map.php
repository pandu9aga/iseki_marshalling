<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$pdo = new PDO('mysql:host=127.0.0.1;dbname=iseki_marshalling;charset=utf8', 'root', '');

$reader = new Xlsx();
$spreadsheet = $reader->load('C:/Users/MCC2104001_003/Downloads/MF1GC - PICKING CARD (MARSHALING).xlsx');

// Find sub_engine sheet
$subEngineSheet = null;
foreach ($spreadsheet->getAllSheets() as $sheet) {
    if (strtolower(trim($sheet->getTitle())) === 'sub_engine') {
        $subEngineSheet = $sheet;
        break;
    }
}

if (!$subEngineSheet) {
    echo "Sheet sub_engine not found!\n";
    exit;
}

$rows = $subEngineSheet->toArray(null, true, true, false);
echo "Checking first 10 data rows of sub_engine:\n";
echo str_pad("KODE RAK(K)", 15) . str_pad("NOMER RAK(L)", 15) . str_pad("Seq from DB", 12) . "\n";
echo str_repeat('-', 50) . "\n";

for ($r = 8; $r < min(20, count($rows)); $r++) {
    $row = $rows[$r];
    $codeRack = trim($row[10] ?? '');
    $locationRack = trim($row[11] ?? '');
    
    if ($codeRack === '' && $locationRack === '') continue;
    
    // Try lookup
    $mapArea = null;
    if ($locationRack !== '') {
        $stmt = $pdo->prepare('SELECT Sequence_No FROM map_areas WHERE Area = ? AND Location_Rack = ? LIMIT 1');
        $stmt->execute(['sub_engine', $locationRack]);
        $mapArea = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $seqNo = $mapArea ? $mapArea['Sequence_No'] : '❌ NOT FOUND';
    echo str_pad($codeRack, 15) . str_pad($locationRack, 15) . str_pad($seqNo, 12) . "\n";
}

echo "\n--- Sample map_areas for sub_engine (first 10) ---\n";
$stmt = $pdo->prepare('SELECT Location_Rack, Sequence_No FROM map_areas WHERE Area = ? ORDER BY Sequence_No LIMIT 10');
$stmt->execute(['sub_engine']);
$mapRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($mapRows as $r) {
    echo "  Location_Rack: " . $r['Location_Rack'] . " => Seq: " . $r['Sequence_No'] . "\n";
}
