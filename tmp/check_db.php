<?php
require_once 'inc/database.php';
$stmt = $pdo->query("SELECT id, title, image FROM spots ORDER BY id DESC LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
