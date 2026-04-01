<?php
require_once 'inc/autoload.inc.php';
require_once 'inc/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();

// Mock a logged in user if none exists
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

try {
    echo "Testing Profile page logic...\n";
    $userModel = new User($pdo);
    $userData  = $userModel->findById($_SESSION['user_id']);
    echo "User found: " . ($userData ? $userData['username'] : 'No user found') . "\n";

    echo "Getting stats...\n";
    $profileStats = $userModel->getStats($_SESSION['user_id']);
    echo "Stats found: " . count($profileStats) . " items\n";

    echo "Getting spots...\n";
    $spotModel = new Spot($pdo);
    $userSpots = $spotModel->getByUser($_SESSION['user_id'], 6);
    echo "Spots found: " . count($userSpots) . " items\n";

    echo "Everything looks okay in the model/database layer.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
