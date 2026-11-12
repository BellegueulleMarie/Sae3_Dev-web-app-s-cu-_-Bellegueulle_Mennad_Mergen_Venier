<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

/**
 * Classe Connection
 *
 * Fabrique la connexion PDO à la base de données MySQL à partir
 * de la configuration fournie dans config/app.php.
 */
final class Connection
{
    /**
     * Crée et retourne une instance PDO configurée.
     *
     * Les options PDO utilisées :
     * - ERRMODE_EXCEPTION : toutes les erreurs SQL lancent une exception
     * - FETCH_ASSOC       : les résultats sont des tableaux associatifs
     * - EMULATE_PREPARES  : désactivé pour utiliser les vraies requêtes préparées
     *
     * @param array{dsn?: string, username?: string, password?: string} $config
     * @throws RuntimeException Si le DSN n'est pas configuré
     */
    public static function create(array $config): PDO
    {
        $dsn = (string) ($config['dsn'] ?? '');
        if ($dsn === '') {
            throw new RuntimeException('La connexion MySQL n\'est pas configuree.');
        }

        return new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}
