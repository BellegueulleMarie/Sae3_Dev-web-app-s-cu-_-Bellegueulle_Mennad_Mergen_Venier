<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class Router
{
    /** @var array<string, callable(array<string, string>): void> */
    private array $routes = [];

    public function __construct(private readonly string $templatePath)
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

    public function put(string $path, callable $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    private function add(string $method, string $path, callable|string $handler): void
    {
        $this->routes[$method . ' ' . $path] = is_string($handler)
            ? function () use ($handler): void {
                $this->render($handler);
            }
            : $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        $route = $this->match($method, $normalizedPath);
        if ($route === null) {
            http_response_code(404);
            echo '<h1>Page introuvable</h1>';
            return;
        }

        [$handler, $parameters] = $route;
        $handler(...array_values($parameters));
    }

    /** @return array{callable, array<string, string>}|null */
    private function match(string $method, string $path): ?array
    {
        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);
            $pattern = preg_replace(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                '(?P<$1>[^/]+)',
                $routePath
            );
            if ($routeMethod === $method && $pattern !== null &&
                preg_match('#^' . $pattern . '/?$#', $path, $matches) === 1) {
                return [$handler, array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)];
            }
        }
        return null;
    }

    private function render(string $template): void
    {
        $file = $this->templatePath . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Template introuvable.');
        }
        require $file;
    }
}
