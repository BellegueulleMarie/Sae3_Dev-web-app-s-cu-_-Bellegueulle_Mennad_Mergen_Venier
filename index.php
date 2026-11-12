<?php

declare(strict_types=1);

use App\Application;

require __DIR__ . '/vendor/autoload.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (PHP_SAPI === 'cli-server' &&
    str_starts_with($requestPath, '/CSS/') &&
    is_file(__DIR__ . $requestPath)) {
    return false;
}

$scriptPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/.');

if (isset($_GET['route']) && is_string($_GET['route'])) {
    $requestPath = '/' . ltrim($_GET['route'], '/');
}

if ($basePath !== '' && $basePath !== '/' &&
    str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
}

if ($scriptPath !== '' && ltrim(rtrim($requestPath, '/'), '/') === basename($scriptPath)) {
    $requestPath = '/';
}

$application = Application::create();
$application['dispatcher']->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
