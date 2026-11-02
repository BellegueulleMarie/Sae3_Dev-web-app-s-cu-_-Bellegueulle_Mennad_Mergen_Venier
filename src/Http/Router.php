<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class Router
{
    /** @var array<string, string> */
    private array $routes = [];

    public function __construct(private readonly string $templatePath)
    {
    }

    public function get(string $path, string $template): void
    {
        $this->routes['GET ' . $path] = $template;
    }

    public function dispatch(string $method, string $path): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        $template = $this->routes[$method . ' ' . $normalizedPath] ?? null;
        if ($template === null) {
            http_response_code(404);
            echo '<h1>Page introuvable</h1>';
            return;
        }

        $file = $this->templatePath . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Template introuvable.');
        }

        require $file;
    }
}
