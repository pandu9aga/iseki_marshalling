<?php
$p = new PDO('mysql:host=localhost;dbname=iseki_marshalling;charset=utf8mb4', 'root', '');
$userIds = $p->query('SELECT DISTINCT Id_User FROM records ORDER BY Id_User')->fetchAll(PDO::FETCH_COLUMN);
echo "User IDs in records: " . implode(', ', $userIds) . "\n";

$p2 = new PDO('mysql:host=localhost;dbname=iseki_rifa;charset=utf8mb4', 'root', '');
foreach ($userIds as $uid) {
    $name = $p2->query("SELECT nama FROM employees WHERE id = $uid")->fetchColumn();
    echo "  User $uid: " . ($name ?: 'NOT FOUND') . "\n";
}
