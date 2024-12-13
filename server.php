<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Esto sirve archivos estáticos directamente cuando existe un archivo en /public
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Sirve la aplicación Laravel
require_once __DIR__ . '/public/index.php';
