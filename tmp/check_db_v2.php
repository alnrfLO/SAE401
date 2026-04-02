<?php
define('DB_HOST',    '127.0.0.1');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_NAME',    'fav');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $stmt = $pdo->query("SELECT id, title, image FROM spots ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
