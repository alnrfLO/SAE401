<?php
spl_autoload_register(function ($class) {
    $dirs = [
        __DIR__ . '/../classes/views/',
        __DIR__ . '/../classes/core/',
        __DIR__ . '/../classes/models/',
        __DIR__ . '/../classes/controllers/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.class.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});