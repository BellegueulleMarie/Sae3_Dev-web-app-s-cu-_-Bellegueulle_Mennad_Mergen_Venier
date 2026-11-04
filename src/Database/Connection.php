<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

final class Connection
{
    /** @param array{dsn?: string, username?: string, password?: string} $config */
    public static function create(array $config): PDO
    {
        $dsn = (string) ($config['dsn'] ?? '');
        if ($dsn === '') {
            throw new RuntimeException('La connexion MySQL n’est pas configuree.');
        }

        return new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
