<?php

declare(strict_types=1);

return [
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost:8000'), '/'),
    'session_name' => $_ENV['SESSION_NAME'] ?? 'sae_session',
    'database' => [
        'dsn' => $_ENV['DB_DSN'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
];
