<?php
define('CHARGE_AUTOLOAD', true);
require_once 'autoload.inc.php';
if (session_status() == PHP_SESSION_NONE) session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$_SESSION['lang'] = $lang;
require_once 'lang/' . $lang . '.php';

switch ($page) {
    case 'home':
        $page = new Home(['page' => 'home']);
    break;
    case 'login':
        $page = new Login(['page' => 'login']);
    break;
    case 'register':
        $page = new Register(['page' => 'register']);
    break;
    case 'spots':
        $page = new Spots(['page' => 'spots']);
    break;
    default:
        $page = new NotFound(['page' => '404']);
    break;
}
echo $page->render();