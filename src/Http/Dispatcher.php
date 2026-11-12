<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

/**
 * Classe Dispatcher
 *
 * Routeur HTTP minimaliste. Il enregistre des routes (GET / POST)
 * et les fait correspondre à l'URL demandée pour appeler le bon handler.
 *
 * Les paramètres d'URL de la forme {id} sont extraits automatiquement
 * et passés au handler sous forme de tableau associatif.
 */
final class Dispatcher
{
    /**
     * Tableau des routes enregistrées.
     * Clé : "METHOD /chemin", valeur : callable attendant array<string, string>
     *
     * @var array<string, callable(array<string, string>): void>
     */
    private array $routes = [];

    /**
     * Constructeur.
     *
     * @param string $viewPath Chemin absolu vers le dossier des vues PHP
     */
    public function __construct(private readonly string $viewPath)
    {
    }

    /**
     * Enregistre une route GET.
     *
     * @param string          $path    Chemin de la route (ex: '/touites/{id}')
     * @param callable|string $handler Callable ou nom de vue à inclure
     */
    public function get(string $path, callable|string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * Enregistre une route POST.
     *
     * @param string   $path    Chemin de la route
     * @param callable $handler Callable à exécuter
     */
    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * Dispatche la requête courante : cherche la route correspondante
     * et appelle son handler avec les paramètres extraits de l'URL.
     * Retourne une 404 si aucune route ne correspond.
     *
     * @param string $method Méthode HTTP (GET, POST…)
     * @param string $path   Chemin de l'URL demandée
     */
    public function dispatch(string $method, string $path): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);
            $pattern = preg_replace(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                '(?P<$1>[^/]+)',
                $routePath
            );
            if ($routeMethod !== $method || $pattern === null ||
                preg_match('#^' . $pattern . '/?$#', $normalizedPath, $matches) !== 1) {
                continue;
            }

            // Garde uniquement les clés string (noms des groupes de capture)
            $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler($parameters);
            return;
        }

        // Aucune route trouvée
        http_response_code(404);
        echo '<h1>Page introuvable</h1>';
    }

    /**
     * Méthode interne : ajoute une route dans le tableau des routes.
     * Si le handler est une chaîne, il est converti en closure qui inclut la vue.
     *
     * @param string          $method  Méthode HTTP
     * @param string          $path    Chemin de la route
     * @param callable|string $handler Callable ou nom de vue
     */
    private function add(string $method, string $path, callable|string $handler): void
    {
        $this->routes[$method . ' ' . $path] = is_string($handler)
            ? function () use ($handler): void {
                $this->render($handler);
            }
            : $handler;
    }

    /**
     * Inclut un fichier de vue PHP.
     *
     * @param string $view Nom de la vue (sans extension, sans chemin)
     * @throws RuntimeException Si le fichier de vue n'existe pas
     */
    private function render(string $view): void
    {
        $file = $this->viewPath . '/' . $view . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Vue introuvable.');
        }
        require $file;
    }
}
