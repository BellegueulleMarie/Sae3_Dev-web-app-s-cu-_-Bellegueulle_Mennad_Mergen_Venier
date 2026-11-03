<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class Dispatcher
{
    /** @var array<string, callable(array<string, string>): void> */
    private array $routes = [];

    public function __construct(private readonly string $viewPath)
    {
    }

    public function get(string $path, callable|string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

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

            $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler($parameters);
            return;
        }

        http_response_code(404);
        echo '<h1>Page introuvable</h1>';
    }

    private function add(string $method, string $path, callable|string $handler): void
    {
        $this->routes[$method . ' ' . $path] = is_string($handler)
            ? function () use ($handler): void {
                $this->render($handler);
            }
            : $handler;
    }

    private function render(string $view): void
    {
        $file = $this->viewPath . '/' . $view . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Vue introuvable.');
        }
        require $file;
    }
}
