import re

def rewrite_controller():
    file_path = 'c:/xampp/htdocs/iseki_marshalling/app/Http/Controllers/Admin/MarshallingController.php'
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    new_import_logic = """
            $imported = 0;
            $skipped = 0;
            
            $allowedAreas = ['front_axle', 'sub_engine', 'transmisi', 'sub_assy', 'main_line', 'inspeksi', 'mowcol'];
            
            // OPTIMIZATION: Preload all MapAreas into memory
            $allMapAreas = \\App\\Models\\MapArea::all();
            $mapAreaDict = [];
            foreach($allMapAreas as $ma) {
                $mapAreaDict[$ma->Area][$ma->Location_Rack] = $ma->Sequence_No;
            }
            
            // OPTIMIZATION: Preload all Types into memory
            $allTypes = \\App\\Models\\Type::all()->pluck('Id_Type', 'Type')->toArray();
            
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
                    
                    if (!isset($allTypes[$typeName])) {
                        $newType = \\App\\Models\\Type::create(['Type' => $typeName]);
                        $allTypes[$typeName] = $newType->Id_Type;
                    }
                    $typeMap[$c] = $allTypes[$typeName];
                }
                
                // Process PER TYPE (column by column)
                foreach ($typeMap as $colIdx => $typeId) {
                    
                    $insertData = [];
                    $now = now();
                    
                    // PRE-READ PENDING INSTRUCTIONS TO KEEP OLD ACTIVE DATA
                    $pendingSeqs = [];
                    for ($r = 8; $r < count($rows); $r++) {
                        $qtyStr = trim($rows[$r][$colIdx] ?? '');
                        if ($qtyStr === '') continue;
                        
                        $noInstruction = trim($rows[$r][14] ?? '');
                        if ($noInstruction !== '') {
                            $locationRackRaw = trim($rows[$r][11] ?? '');
                            $locationRack = strpos($locationRackRaw, ',') !== false ? trim(explode(',', $locationRackRaw)[0]) : $locationRackRaw;
                            $codeRack = trim($rows[$r][10] ?? '');
                            $subArea = trim($rows[$r][13] ?? '');
                            $area = $baseArea;
                            if ($subArea !== '') {
                                $area .= '_' . strtolower($subArea);
                            }
                            
                            $sequenceNo = 0;
                            if ($locationRack !== '' && isset($mapAreaDict[$area][$locationRack])) {
                                $sequenceNo = $mapAreaDict[$area][$locationRack];
                            } elseif ($codeRack !== '' && isset($mapAreaDict[$area][$codeRack])) {
                                $sequenceNo = $mapAreaDict[$area][$codeRack];
                            }
                            if ($sequenceNo > 0) {
                                $pendingSeqs[] = $sequenceNo;
                            }
                        }
                    }
                    
                    // Fetch old active parts that belong to pending Sequence_Nos
                    $oldActivePartsToKeep = [];
                    if (!empty($pendingSeqs)) {
                        foreach ($sheetAreas as $area) {
                            $oldParts = Marshalling::where('Id_Type', $typeId)
                                ->where('Area', $area)
                                ->whereIn('Sequence_No', array_unique($pendingSeqs))
                                ->where('Is_Active', 1)
                                ->get()
                                ->toArray();
                            $oldActivePartsToKeep = array_merge($oldActivePartsToKeep, $oldParts);
                        }
                    }
                    
                    // DELETE EVERYTHING FOR THIS TYPE & AREA
                    foreach ($sheetAreas as $area) {
                        Marshalling::where('Id_Type', $typeId)->where('Area', $area)->delete();
                    }
                    
                    $excelKeys = [];
                    
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
                            // Only count skipped rows once per sheet
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
                        
                        // OPTIMIZATION: Memory lookup instead of DB query
                        $sequenceNo = 0;
                        if ($locationRack !== '' && isset($mapAreaDict[$area][$locationRack])) {
                            $sequenceNo = $mapAreaDict[$area][$locationRack];
                        } elseif ($codeRack !== '' && isset($mapAreaDict[$area][$codeRack])) {
                            $sequenceNo = $mapAreaDict[$area][$codeRack];
                        }
                        
                        $isActive = ($noInstruction === '') ? 1 : 0;
                        
                        // Parse difference overrides if any
                        $diffOverrides = [];
                        if ($difference !== '') {
                            $diffParts = explode(';', $difference);
                            foreach ($diffParts as $dp) {
                                if (strpos($dp, ':') !== false) {
                                    [$tName, $boxQtys] = explode(':', $dp, 2);
                                    $tName = trim($tName);
                                    
                                    if (!isset($allTypes[$tName])) {
                                        $newT = \\App\\Models\\Type::create(['Type' => $tName]);
                                        $allTypes[$tName] = $newT->Id_Type;
                                    }
                                    $tId = $allTypes[$tName];
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
                            ];
                            $key = $sequenceNo . '_' . $codePart . '_' . $namePart . '_' . $entry['box'];
                            $excelKeys[$key] = true;
                            $imported++;
                        }
                    }
                    
                    // Re-insert the old active parts that belong to pending instructions
                    // ONLY if they weren't also in the Excel to prevent duplicates
                    foreach ($oldActivePartsToKeep as $oldPart) {
                        $key = $oldPart['Sequence_No'] . '_' . $oldPart['Code_Part'] . '_' . $oldPart['Name_Part'] . '_' . $oldPart['Box'];
                        if (!isset($excelKeys[$key])) {
                            unset($oldPart['Id_Marshalling']);
                            $insertData[] = $oldPart;
                        }
                    }
                    
                    // Bulk insert in chunks
                    foreach (array_chunk($insertData, 500) as $chunk) {
                        Marshalling::insert($chunk);
                    }
                }
            }
"""

    start_str = "$imported = 0;\n            $skipped = 0;"
    end_str = "foreach (array_chunk($insertData, 500) as $chunk) {\n                        Marshalling::insert($chunk);\n                    }\n                }\n            }"

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
