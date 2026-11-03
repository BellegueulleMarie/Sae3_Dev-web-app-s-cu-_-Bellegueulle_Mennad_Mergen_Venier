<?php

declare(strict_types=1);

use App\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$application = Bootstrap::create();
$application['dispatcher']->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
