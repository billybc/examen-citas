<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/') {
    $uri = '/index.html';
}

if (!str_starts_with($uri, '/api') && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false; // deja que el servidor embebido sirva el archivo estático tal cual
}

require __DIR__ . '/api.php';
