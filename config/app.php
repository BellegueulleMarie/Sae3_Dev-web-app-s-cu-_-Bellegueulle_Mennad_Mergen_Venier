<?php

declare(strict_types=1);

return [
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'session_name' => $_ENV['SESSION_NAME'] ?? 'sae_session',
    'image_upload_directory' => $_ENV['IMAGE_UPLOAD_DIRECTORY']
        ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'touiteur-uploads',
    'database' => [
        'dsn' => $_ENV['DB_DSN'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
];
