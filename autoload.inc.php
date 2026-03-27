<?php
spl_autoload_register(function ($class) {
    $dirs = [
        __DIR__ . '/app/views/',
        __DIR__ . '/core/',
    ];
    
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.class.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});