<?php
define('CHARGE_AUTOLOAD', true);
require_once 'autoload.inc.php';
if (session_status() == PHP_SESSION_NONE) session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$_SESSION['lang'] = $lang;
require_once '../lang/' . $lang . '.php';

switch ($page) {
    case 'home':
        $page = new Home();
    break;
    case 'login':
        $page = new Login();
    break;
    case 'spots':
        $page = new Spots();
    break;
    case 'register':
        $page = new Register();
    break;
    default:
        $page = new Home();
    break;
}
echo $page->render();