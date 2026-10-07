<?php
require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;dbname=iseki_marshalling;charset=utf8', 'root', '');
$rows = $pdo->query('SELECT Id_Marshalling, Area, Sequence_No, Code_Part, Code_Rack, No_Instruction, Is_Active FROM marshallings WHERE Area=\'sub_engine\' AND Id_Type=10786')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo 'ID: ' . $r['Id_Marshalling'] . ' | Code: ' . $r['Code_Part'] . ' | Seq: ' . $r['Sequence_No'] . ' | NoInst: ' . ($r['No_Instruction'] ?? 'NULL') . ' | Active: ' . $r['Is_Active'] . "\n";
}
