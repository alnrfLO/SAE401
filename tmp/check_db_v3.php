<?php
define('DB_HOST',    '127.0.0.1');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_NAME',    'fav');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $stmt = $pdo->query("SELECT * FROM spots ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    // Try with localhost if 127.0.0.1 fails
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        $stmt = $pdo->query("SELECT * FROM spots ORDER BY id DESC LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows, JSON_PRETTY_PRINT);
    } catch (PDOException $e2) {
        echo "Connection failed: " . $e2->getMessage();
    }
}
