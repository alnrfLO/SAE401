<?php
define('CHARGE_AUTOLOAD', true);
require_once 'autoload.inc.php';
if (session_status() == PHP_SESSION_NONE) session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'home':
        $page = new Home();
    break;
    case 'login':
        $page = new Login();
    break;
    default:
        $page = new Home();
    break;
}
echo $page->render();