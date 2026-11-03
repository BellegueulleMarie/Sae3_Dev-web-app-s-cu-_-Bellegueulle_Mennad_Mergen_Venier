<?php

declare(strict_types=1);

use App\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$application = Bootstrap::create();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$application['router']->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
