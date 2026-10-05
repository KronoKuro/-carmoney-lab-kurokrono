<?php

declare(strict_types=1);

/**
 * Роутер для встроенного сервера PHP (php -S).
 * API отдаёт Slim, статику формы — этот файл из каталога frontend/.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$frontendDir = dirname(__DIR__, 2) . '/frontend';

$staticFiles = [
    '/' => ['index.html', 'text/html; charset=utf-8'],
    '/index.html' => ['index.html', 'text/html; charset=utf-8'],
    '/app.js' => ['app.js', 'application/javascript; charset=utf-8'],
    '/styles.css' => ['styles.css', 'text/css; charset=utf-8'],
];

if (isset($staticFiles[$path])) {
    [$file, $contentType] = $staticFiles[$path];
    $fullPath = $frontendDir . '/' . $file;

    if (is_file($fullPath)) {
        header('Content-Type: ' . $contentType);
        readfile($fullPath);

        return;
    }
}

require __DIR__ . '/index.php';
