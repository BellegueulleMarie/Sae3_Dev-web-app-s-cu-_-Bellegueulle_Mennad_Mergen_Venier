<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class Router
{
    /** @var array<string, callable(): void> */
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
        $handler = $this->routes[$method . ' ' . $normalizedPath] ?? null;
        if ($handler === null) {
            http_response_code(404);
            echo '<h1>Page introuvable</h1>';
            return;
        }

        $handler();
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
