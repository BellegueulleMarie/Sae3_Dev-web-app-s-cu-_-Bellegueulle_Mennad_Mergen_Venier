<?php

declare(strict_types=1);

namespace App;

use App\Http\Dispatcher;
use App\Http\JsonResponse;
use App\Http\RequestDataReader;
use App\Database\Connection;
use App\Repository\UtilisateurRepository;
use App\Action\HomeAction;
use App\Action\UtilisateurCreateAction;
use App\Action\UtilisateurListAction;
use App\Action\UtilisateurShowAction;
use App\Action\UtilisateurUpdateAction;
use RuntimeException;
use Throwable;

final class Bootstrap
{
    /** @return array{config: array<string, mixed>, dispatcher: Dispatcher} */
    public static function create(): array
    {
        $root = dirname(__DIR__);
        self::loadEnvironment($root . '/.env');

        $config = require $root . '/config/app.php';
        self::configureErrors((bool) $config['debug']);
        self::startSession((string) $config['session_name']);
        $dispatcher = new Dispatcher($root . '/views');
        $dispatcher->get('/', [new HomeAction(), 'execute']);
        if ((string) ($config['database']['dsn'] ?? '') !== '') {
            $database = Connection::create($config['database']);
            $request = new RequestDataReader();
            $response = new JsonResponse();
            $repository = UtilisateurRepository::getInstance($database);
            $dispatcher->get('/api/utilisateurs', [new UtilisateurListAction($repository, $response), 'execute']);
            $dispatcher->get('/api/utilisateurs/{id}', [new UtilisateurShowAction($repository, $response), 'execute']);
            $dispatcher->post('/api/utilisateurs', [new UtilisateurCreateAction($repository, $request, $response), 'execute']);
            $dispatcher->put('/api/utilisateurs/{id}', [new UtilisateurUpdateAction($repository, $request, $response), 'execute']);
        } else {
            $databaseUnavailable = static function (): void {
                (new JsonResponse())->send([
                    'error' => 'La connexion à la base de données n’est pas configurée.',
                ], 503);
            };
            $dispatcher->get('/api/utilisateurs', $databaseUnavailable);
            $dispatcher->get('/api/utilisateurs/{id}', $databaseUnavailable);
            $dispatcher->post('/api/utilisateurs', $databaseUnavailable);
            $dispatcher->put('/api/utilisateurs/{id}', $databaseUnavailable);
        }

        return ['config' => $config, 'dispatcher' => $dispatcher];
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
