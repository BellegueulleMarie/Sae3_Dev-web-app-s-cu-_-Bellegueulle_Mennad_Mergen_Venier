<?php

declare(strict_types=1);

namespace App;

use App\Http\Dispatcher;
use App\Database\Connection;
use App\Repository\TouiteRepository;
use App\Repository\UtilisateurRepository;
use App\Action\UtilisateurCreateAction;
use App\Action\HomeAction;
use App\Action\UtilisateurListAction;
use App\Action\UtilisateurShowAction;
use App\Action\UtilisateurUpdateAction;
use App\Action\UtilisateurFollowAction;
use App\Action\TouiteListAction;
use App\Action\TouiteShowAction;
use App\Action\UtilisateurProfileAction;
use App\Action\UtilisateurPageAction;
use App\Action\UtilisateurLoginAction;
use App\Action\UtilisateurLogoutAction;
use RuntimeException;
use Throwable;

final class Application
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
        if ((string) ($config['database']['dsn'] ?? '') !== '') {
            $database = Connection::create($config['database']);
            $repository = UtilisateurRepository::getInstance($database);
            $touiteRepository = TouiteRepository::getInstance($database);
            $dispatcher->get('/', [new HomeAction($touiteRepository), 'execute']);
            $dispatcher->get('/touites', [new TouiteListAction($touiteRepository), 'execute']);
            $dispatcher->get('/touites/{id}', [new TouiteShowAction($touiteRepository), 'execute']);
            $dispatcher->get('/utilisateurs/{id}/profil', [new UtilisateurProfileAction($repository), 'execute']);
            $dispatcher->get('/utilisateurs', [new UtilisateurPageAction($repository), 'execute']);
            $dispatcher->get('/connexion', [new UtilisateurLoginAction($repository), 'execute']);
            $dispatcher->post('/connexion', [new UtilisateurLoginAction($repository), 'execute']);
            $dispatcher->get('/deconnexion', [new UtilisateurLogoutAction(), 'execute']);
            $dispatcher->get('/api/utilisateurs', [new UtilisateurListAction($repository), 'execute']);
            $dispatcher->get('/api/utilisateurs/{id}', [new UtilisateurShowAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs', [new UtilisateurCreateAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs/{id}/modifier', [new UtilisateurUpdateAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs/{id}/suivre', [new UtilisateurFollowAction($repository), 'execute']);
            $dispatcher->get('/utilisateurs/creation', [new UtilisateurCreateAction($repository), 'execute']);
            $dispatcher->post('/utilisateurs/creation', [new UtilisateurCreateAction($repository), 'execute']);
        } else {
            $dispatcher->get('/', [new HomeAction(), 'execute']);
            $databaseUnavailable = static function (): void {
                echo '<h1>Base de données non configurée.</h1>';
            };
            $dispatcher->get('/api/utilisateurs', $databaseUnavailable);
            $dispatcher->get('/api/utilisateurs/{id}', $databaseUnavailable);
            $dispatcher->post('/api/utilisateurs', $databaseUnavailable);
            $dispatcher->post('/api/utilisateurs/{id}/modifier', $databaseUnavailable);
            $dispatcher->get('/utilisateurs/creation', $databaseUnavailable);
            $dispatcher->post('/utilisateurs/creation', $databaseUnavailable);
            $dispatcher->get('/touites', $databaseUnavailable);
            $dispatcher->get('/touites/{id}', $databaseUnavailable);
            $dispatcher->get('/utilisateurs/{id}/profil', $databaseUnavailable);
            $dispatcher->get('/utilisateurs', $databaseUnavailable);
            $dispatcher->get('/connexion', $databaseUnavailable);
            $dispatcher->post('/connexion', $databaseUnavailable);
            $dispatcher->get('/deconnexion', [new UtilisateurLogoutAction(), 'execute']);
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
        session_start();
    }
}
