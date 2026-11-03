<?php

declare(strict_types=1);

namespace App;

use App\Http\Router;
use App\Database\Connection;
use App\Dao\TagDao;
use App\Dao\TouiteDao;
use App\Dao\UtilisateurDao;
use App\Http\TagController;
use App\Http\TouiteController;
use App\Http\UtilisateurController;
use App\Service\TagService;
use App\Service\TouiteService;
use App\Service\UtilisateurService;
use App\Security\SecurityHeaders;
use RuntimeException;
use Throwable;

final class Bootstrap
{
    /** @return array{config: array<string, mixed>, router: Router} */
    public static function create(): array
    {
        $root = dirname(__DIR__);
        self::loadEnvironment($root . '/.env');

        $config = require $root . '/config/app.php';
        self::configureErrors((bool) $config['debug']);
        self::startSession((string) $config['session_name']);
        SecurityHeaders::send();

        $router = new Router($root . '/templates');
        $router->get('/', 'home');

        if ((string) ($config['database']['dsn'] ?? '') !== '') {
            $database = Connection::create($config['database']);
            $users = new UtilisateurController(new UtilisateurService(new UtilisateurDao($database)));
            $tags = new TagController(new TagService(new TagDao($database)));
            $touites = new TouiteController(new TouiteService(new TouiteDao($database)));

            $router->get('/api/utilisateurs', [$users, 'index']);
            $router->post('/api/utilisateurs', [$users, 'store']);
            $router->get('/api/tags', [$tags, 'index']);
            $router->post('/api/tags', [$tags, 'store']);
            $router->get('/api/touites', [$touites, 'index']);
            $router->post('/api/touites', [$touites, 'store']);
        }

        return ['config' => $config, 'router' => $router];
    }

    private static function loadEnvironment(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if ($key !== '' && getenv($key) === false) {
                $_ENV[$key] = $value;
            }
        }
    }

    private static function configureErrors(bool $debug): void
    {
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting($debug ? E_ALL : 0);

        set_exception_handler(static function (Throwable $exception) use ($debug): void {
            error_log((string) $exception);
            http_response_code(500);
            if ($debug) {
                echo '<h1>Erreur interne</h1><pre>' .
                    htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') .
                    '</pre>';
                return;
            }
            echo '<h1>Une erreur est survenue.</h1>';
        });
    }

    private static function startSession(string $name): void
    {
        if ($name === '') {
            throw new RuntimeException('Le nom de session ne peut pas être vide.');
        }

        session_name($name);
        session_set_cookie_params([
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start();
    }
}
