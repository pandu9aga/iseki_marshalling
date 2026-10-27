import re

def rewrite_controller():
    file_path = 'c:/xampp/htdocs/iseki_marshalling/app/Http/Controllers/Admin/MarshallingController.php'
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    new_import_logic = """
            $imported = 0;
            $skipped = 0;
            
            $allowedAreas = ['front_axle', 'sub_engine', 'transmisi', 'sub_assy', 'main_line', 'inspeksi', 'mowcol'];
            
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetTitle = strtolower(trim($sheet->getTitle()));
                $baseArea = ($sheetTitle === 'mower' || $sheetTitle === 'collector') ? 'mowcol' : $sheetTitle;
                
                if (!in_array($baseArea, $allowedAreas)) continue;
                
                $rows = $sheet->toArray(null, true, true, false);
                if (count($rows) < 9) continue;
                
                // Collect all areas in this sheet
                $areasInSheet = [];
                for ($r = 8; $r < count($rows); $r++) {
                    $subArea = trim($rows[$r][13] ?? '');
                    $area = $baseArea;
                    if ($subArea !== '') {
                        $area .= '_' . strtolower($subArea);
                    }
                    $areasInSheet[$area] = true;
                }
                $sheetAreas = array_keys($areasInSheet);
                
                // Parse headers at row 8 (index 7)
                $headerRow = $rows[7];
                $typeMap = [];
                // Col R is index 17
                for ($c = 17; $c < count($headerRow); $c++) {
                    $typeName = trim($headerRow[$c] ?? '');
                    if ($typeName === '') break; // STOP at first empty column
                    
                    $type = Type::firstOrCreate(['Type' => $typeName]);
                    $typeMap[$c] = $type->Id_Type;
                }
                
                // Process PER TYPE (column by column)
                foreach ($typeMap as $colIdx => $typeId) {
                    
                    // Delete old data for this Type and Areas
                    foreach ($sheetAreas as $area) {
                        Marshalling::where('Id_Type', $typeId)->where('Area', $area)->delete();
                    }
                    
                    $insertData = [];
                    $now = now();
                    
                    for ($r = 8; $r < count($rows); $r++) {
                        $row = $rows[$r];
                        
                        $blok = trim($row[2] ?? ''); // C (BLOK)
                        $codePart = trim($row[3] ?? ''); // D (CODE NUMBER)
                        $namePart = trim($row[4] ?? ''); // E (NAME OF PART)
                        $codeRack = trim($row[10] ?? ''); // K (KODE RAK)
                        
                        $locationRackRaw = trim($row[11] ?? ''); // L (NOMER RAK)
                        $locationRack = strpos($locationRackRaw, ',') !== false ? trim(explode(',', $locationRackRaw)[0]) : $locationRackRaw;
                        
                        $skipMark = trim($row[12] ?? ''); // M (PART DI LUAR AREA)
                        $subArea = trim($row[13] ?? ''); // N (KODE LIST)
                        $noInstruction = trim($row[14] ?? ''); // O (NO INTRUKSI)
                        $box = trim($row[15] ?? ''); // P (BOX)
                        $difference = trim($row[16] ?? ''); // Q (PEMBEDA)
                        
                        if ($skipMark !== '' || $codePart === '') {
                            // Only count skipped rows once per sheet (on the first type column)
                            if ($colIdx === 17) $skipped++;
                            continue;
                        }
                        
                        $qtyStr = trim($row[$colIdx] ?? '');
                        if ($qtyStr === '') continue;
                        
                        // Check if cell is strikethrough (can be slow, but necessary)
                        $isStrikethrough = $sheet->getCell([$colIdx + 1, $r + 1])->getStyle()->getFont()->getStrikethrough();
                        if ($isStrikethrough) continue;
                        
                        $area = $baseArea;
                        if ($subArea !== '') {
                            $area .= '_' . strtolower($subArea);
                        }
                        
                        $mapArea = null;
                        if ($locationRack !== '') {
                            $mapArea = \\App\\Models\\MapArea::where('Area', $area)->where('Location_Rack', $locationRack)->first();
                        }
                        if (!$mapArea && $codeRack !== '') {
                            $mapArea = \\App\\Models\\MapArea::where('Area', $area)->where('Location_Rack', $codeRack)->first();
                        }
                        $sequenceNo = $mapArea ? $mapArea->Sequence_No : 0;
                        
                        $isActive = ($noInstruction === '') ? 1 : 0;
                        
                        // Parse difference overrides if any
                        $diffOverrides = [];
                        if ($difference !== '') {
                            $diffParts = explode(';', $difference);
                            foreach ($diffParts as $dp) {
                                if (strpos($dp, ':') !== false) {
                                    [$tName, $boxQtys] = explode(':', $dp, 2);
                                    $tName = trim($tName);
                                    $typeObj = Type::firstOrCreate(['Type' => $tName]);
                                    $tId = $typeObj->Id_Type;
                                    $diffOverrides[$tId] = [];
                                    
                                    $bqParts = explode(',', $boxQtys);
                                    foreach ($bqParts as $bq) {
                                        if (strpos($bq, '=') !== false) {
                                            [$bName, $bQty] = explode('=', $bq, 2);
                                            $diffOverrides[$tId][] = ['box' => trim($bName), 'qty' => (int)trim($bQty)];
                                        }
                                    }
                                }
                            }
                        }
                        
                        $entries = [];
                        if (isset($diffOverrides[$typeId]) && count($diffOverrides[$typeId]) > 0) {
                            foreach ($diffOverrides[$typeId] as $ov) {
                                $entries[] = ['box' => $ov['box'], 'qty' => $ov['qty']];
                            }
                        } elseif (is_numeric($qtyStr) && $qtyStr > 0) {
                            $entries[] = ['box' => $box, 'qty' => (int)$qtyStr];
                        }
                        
                        foreach ($entries as $entry) {
                            $insertData[] = [
                                'Id_Type' => $typeId,
                                'Sequence_No' => $sequenceNo,
                                'Code_Part' => $codePart,
                                'Name_Part' => $namePart,
                                'Code_Rack' => $codeRack,
                                'Difference' => $difference,
                                'Location_Rack' => $locationRack,
                                'Box' => $entry['box'],
                                'Qty' => $entry['qty'],
                                'Mode' => 'manual',
                                'Area' => $area,
                                'No_Instruction' => $noInstruction !== '' ? $noInstruction : null,
                                'Is_Active' => $isActive,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $imported++;
                        }
                    }
                    
                    // Bulk insert in chunks
                    foreach (array_chunk($insertData, 500) as $chunk) {
                        Marshalling::insert($chunk);
                    }
                }
            }
            
            $msg = "$imported marshallings imported/updated.";
            if ($skipped > 0) $msg .= " $skipped rows skipped.";
"""

    start_str = "$imported = 0;\n            $skipped = 0;\n            $newActiveKeys = [];"
    end_str = "$msg = \"$imported marshallings imported/updated.\";\n            if ($deactivated > 0) $msg .= \" $deactivated marshalling lama di-nonaktifkan.\";\n            if ($skipped > 0) $msg .= \" $skipped rows skipped.\";"

    start_idx = content.find(start_str)
    end_idx = content.find(end_str) + len(end_str)
    
    if start_idx != -1 and end_idx != -1:
        new_content = content[:start_idx] + new_import_logic.strip() + content[end_idx:]
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print("Success")
    else:
        print("Failed to find replacement indices")

rewrite_controller()
