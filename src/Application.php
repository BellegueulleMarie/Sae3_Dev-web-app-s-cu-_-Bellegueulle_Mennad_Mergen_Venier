<?php

declare(strict_types=1);

namespace App;

use App\Http\Dispatcher;
use App\Database\Connection;
use App\Repository\TouiteRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\TagRepository;
use App\Action\UtilisateurCreateAction;
use App\Action\HomeAction;
use App\Action\UtilisateurListAction;
use App\Action\UtilisateurShowAction;
use App\Action\UtilisateurUpdateAction;
use App\Action\UtilisateurFollowAction;
use App\Action\TouiteListAction;
use App\Action\TouiteShowAction;
use App\Action\TouitePublishAction;
use App\Action\TouiteImageAction;
use App\Action\UtilisateurProfileAction;
use App\Action\UtilisateurPageAction;
use App\Action\UtilisateurLoginAction;
use App\Action\UtilisateurLogoutAction;
use App\Action\TagPageAction;
use App\Action\TagSubscribeAction;
use App\Action\StatisticsAction;
use RuntimeException;
use Throwable;
use App\Action\TouiteEvaluateAction;
use App\Action\AdminFollowersAction;
use App\Action\AdminDashboardAction;
use App\Action\AdminInfluencerAction;
use App\Action\AdminLoginAction;
use App\Action\AdminLogoutAction;
use App\Action\AdminTrendAction;

/**
 * Classe Application
 *
 * Point d'entrée central et configurateur de l'application Touiteur.
 * Initialise l'environnement, configure la gestion d'erreurs, démarre la session,
 * instancie les dépendances (repositories, connexion BDD) et enregistre toutes les routes dans le Dispatcher.
 */
final class Application
{
    /**
     * Initialise l'application et retourne sa configuration ainsi que le dispatcher prêt à l'emploi.
     *
     * @return array{config: array<string, mixed>, dispatcher: Dispatcher}
     */
    public static function create(bool $adminPortal = false): array
    {
        $root = dirname(__DIR__);
        self::loadEnvironment($root . '/.env');

        $config = require $root . '/config/app.php';
        self::configureErrors((bool) $config['debug']);
        self::startSession(
            (string) ($adminPortal ? $config['admin_session_name'] : $config['session_name']),
            $adminPortal
        );
        if ($adminPortal) {
            header('Cache-Control: no-store, private');
            header('X-Content-Type-Options: nosniff');
        }
        $dispatcher = new Dispatcher($root . '/views');
        if ((string) ($config['database']['dsn'] ?? '') !== '') {
            $database = Connection::create($config['database']);
            $repository = UtilisateurRepository::getInstance($database);
            if ($adminPortal) {
                $tagRepository = TagRepository::getInstance($database);
                $dispatcher->get('/', [new AdminDashboardAction($repository), 'execute']);
                $dispatcher->get('/connexion', [new AdminLoginAction($repository), 'execute']);
                $dispatcher->post('/connexion', [new AdminLoginAction($repository), 'execute']);
                $dispatcher->get('/deconnexion', [new AdminLogoutAction(), 'execute']);
                $dispatcher->get('/utilisateurs-suivis', [new AdminFollowersAction($repository), 'execute']);
                $dispatcher->get('/influenceurs', [new AdminInfluencerAction($repository), 'execute']);
                $dispatcher->get('/tendances', [new AdminTrendAction($repository, $tagRepository), 'execute']);

                return ['config' => $config, 'dispatcher' => $dispatcher];
            }

            $touiteRepository = TouiteRepository::getInstance($database);
            $dispatcher->post('/api/touites/{id}/evaluer', [new TouiteEvaluateAction($touiteRepository), 'execute']);
            $tagRepository = TagRepository::getInstance($database);
            $dispatcher->get('/', [new HomeAction($touiteRepository), 'execute']);
            $dispatcher->get('/touites', [new TouiteListAction($touiteRepository), 'execute']);
            $imageDirectory = (string) $config['image_upload_directory'];
            $dispatcher->get('/touites/nouveau', [new TouitePublishAction($touiteRepository, $imageDirectory), 'execute']);
            $dispatcher->post('/touites/publier', [new TouitePublishAction($touiteRepository, $imageDirectory), 'execute']);
            $dispatcher->get('/touites/{id}', [new TouiteShowAction($touiteRepository, $repository), 'execute']);
            $dispatcher->get('/touites/{id}/image', [new TouiteImageAction($touiteRepository, $imageDirectory), 'execute']);
            $dispatcher->get('/utilisateurs/{id}/profil', [new UtilisateurProfileAction($repository, $touiteRepository), 'execute']);
            $dispatcher->get('/utilisateurs', [new UtilisateurPageAction($repository), 'execute']);
            $dispatcher->get('/tags', [new TagPageAction($tagRepository, $touiteRepository), 'execute']);
            $dispatcher->get('/tags/{id}', [new TagPageAction($tagRepository, $touiteRepository), 'execute']);
            $dispatcher->get('/statistiques', [new StatisticsAction($tagRepository, $touiteRepository, $repository), 'execute']);
            $dispatcher->get('/influenceurs', static function (): void {
                header('Location: admin.php?route=influenceurs');
            });
            $dispatcher->get('/tendances', static function (): void {
                header('Location: admin.php?route=tendances');
            });
            $dispatcher->get('/administration/utilisateurs-suivis', static function (): void {
                header('Location: admin.php?route=utilisateurs-suivis');
            });
            $dispatcher->post('/api/tags/abonner', [new TagSubscribeAction($tagRepository), 'execute']);
            $dispatcher->post('/api/tags/{id}/abonner', [new TagSubscribeAction($tagRepository), 'execute']);
            $dispatcher->post('/api/tags/{id}/desabonner', [new TagSubscribeAction($tagRepository), 'execute']);
            $dispatcher->get('/connexion', [new UtilisateurLoginAction($repository), 'execute']);
            $dispatcher->post('/connexion', [new UtilisateurLoginAction($repository), 'execute']);
            $dispatcher->get('/deconnexion', [new UtilisateurLogoutAction(), 'execute']);
            $dispatcher->get('/api/utilisateurs', [new UtilisateurListAction($repository), 'execute']);
            $dispatcher->get('/api/utilisateurs/{id}', [new UtilisateurShowAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs', [new UtilisateurCreateAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs/{id}/modifier', [new UtilisateurUpdateAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs/{id}/suivre', [new UtilisateurFollowAction($repository), 'execute']);
            $dispatcher->post('/api/utilisateurs/{id}/desabonner', [new UtilisateurFollowAction($repository), 'execute']);
            $dispatcher->get('/utilisateurs/creation', [new UtilisateurCreateAction($repository), 'execute']);
            $dispatcher->post('/utilisateurs/creation', [new UtilisateurCreateAction($repository), 'execute']);
        } else {
            if ($adminPortal) {
                $databaseUnavailable = static function (): void {
                    http_response_code(503);
                    echo '<h1>Base de données non configurée.</h1>';
                };
                $dispatcher->get('/', $databaseUnavailable);
                $dispatcher->get('/connexion', $databaseUnavailable);
                $dispatcher->post('/connexion', $databaseUnavailable);
                $dispatcher->get('/deconnexion', $databaseUnavailable);
                $dispatcher->get('/utilisateurs-suivis', $databaseUnavailable);
                $dispatcher->get('/influenceurs', $databaseUnavailable);
                $dispatcher->get('/tendances', $databaseUnavailable);

                return ['config' => $config, 'dispatcher' => $dispatcher];
            }

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
            $dispatcher->get('/touites/{id}/image', $databaseUnavailable);
            $dispatcher->post('/api/touites/{id}/evaluer', $databaseUnavailable);
            $dispatcher->get('/utilisateurs/{id}/profil', $databaseUnavailable);
            $dispatcher->get('/utilisateurs', $databaseUnavailable);
            $dispatcher->get('/statistiques', $databaseUnavailable);
            $dispatcher->get('/administration/utilisateurs-suivis', $databaseUnavailable);
            $dispatcher->get('/influenceurs', $databaseUnavailable);
            $dispatcher->get('/tendances', $databaseUnavailable);
            $dispatcher->get('/tags/{id}', $databaseUnavailable);
            $dispatcher->get('/connexion', $databaseUnavailable);
            $dispatcher->post('/connexion', $databaseUnavailable);
            $dispatcher->get('/deconnexion', [new UtilisateurLogoutAction(), 'execute']);
        }

        return ['config' => $config, 'dispatcher' => $dispatcher];
    }

    /**
     * Charge les variables d'environnement depuis un fichier .env s'il existe.
     *
     * @param string $path Chemin absolu vers le fichier .env
     */
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

    /**
     * Configure l'affichage des erreurs PHP et le gestionnaire d'exceptions global.
     *
     * @param bool $debug Indique si le mode débogage est actif
     */
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

    /**
     * Démarre la session PHP avec le nom spécifié.
     *
     * @param string $name Nom de session
     * @throws RuntimeException Si le nom de session est vide
     */
    private static function startSession(string $name, bool $adminPortal = false): void
    {
        if ($name === '') {
            throw new RuntimeException('Le nom de session ne peut pas être vide.');
        }

        session_name($name);
        if ($adminPortal) {
            ini_set('session.use_strict_mode', '1');
            session_set_cookie_params([
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Strict',
                'path' => '/',
            ]);
        }
        session_start();
    }
}
