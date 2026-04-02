<?php
// ─────────────────────────────────────────────────────────────
//  Configuration de la base de données — FAV Project
//  Utilise PDO avec options de sécurité maximales
// ─────────────────────────────────────────────────────────────

// En production, ces valeurs devraient venir de variables d'env
// Ex: define('DB_HOST', $_ENV['DB_HOST']);
define('DB_HOST',    'localhost');

define('DB_USER',    'fav');
define('DB_PASS',    'fav2026');
define('DB_NAME',    'fav');
define('DB_CHARSET', 'utf8mb4');

// Durée max d'une session (en secondes) — 7 jours
define('SESSION_LIFETIME', 60 * 60 * 24 * 7);

// Options PDO sécurisées
$dsn     = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
// Options PDO sécurisées
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lance des exceptions sur erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Retourne des tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                     // Vraies requêtes préparées (protection injection SQL)
];

// PHP 8.1+ triggers deprecation for PDO::MYSQL_ATTR_FOUND_ROWS
if (defined('PDO::MYSQL_ATTR_FOUND_ROWS')) {
    $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
} else if (defined('Pdo\Mysql::ATTR_FOUND_ROWS')) {
    $options[Pdo\Mysql::ATTR_FOUND_ROWS] = true;
}

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En production : logger l'erreur, ne PAS l'afficher
    error_log('[FAV DB Error] ' . $e->getMessage());
    http_response_code(503);
    die(json_encode(['error' => 'Service temporairement indisponible.']));
}