<?php
spl_autoload_register(function ($class) {
    $dirs = [
        __DIR__ . '/../app/views/home/',
        __DIR__ . '/../app/views/auth/',
        __DIR__ . '/../app/views/spots/',
        __DIR__ . '/../app/views/comments/',
        __DIR__ . '/../core/',
        __DIR__ . '/../app/views/errors/',
    ];
    
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.class.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});