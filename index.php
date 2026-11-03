<?php

declare(strict_types=1);

use App\Bootstrap;

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($requestPath, '/assets/')) {
    $asset = realpath(__DIR__ . $requestPath);
    $assetsRoot = realpath(__DIR__ . '/assets');
    if ($asset !== false && $assetsRoot !== false && str_starts_with($asset, $assetsRoot . DIRECTORY_SEPARATOR) && is_file($asset)) {
        $mimeTypes = ['css' => 'text/css; charset=utf-8', 'js' => 'application/javascript; charset=utf-8'];
        $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            readfile($asset);
            exit;
        }
    }
    http_response_code(404);
    exit;
}

require __DIR__ . '/vendor/autoload.php';

$application = Bootstrap::create();
$application['dispatcher']->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
