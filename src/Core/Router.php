<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, callable> regular expression => handler */
    private array $routes = [];

    /**
     * Placeholders like {slug} match lowercase latin letters and digits separated by single dashes.
     */
    public function add(string $pattern, callable $handler): void
    {
        $regex = '#^' . preg_replace('/\{\w+\}/', '([a-z0-9]+(?:-[a-z0-9]+)*)', $pattern) . '$#';

        $this->routes[$regex] = $handler;
    }

    /**
     * @throws NotFoundException
     */
    public function dispatch(string $path): string
    {
        foreach ($this->routes as $regex => $handler) {
            if (preg_match($regex, $path, $matches)) {
                return $handler(...array_slice($matches, 1));
            }
        }

        throw new NotFoundException();
    }
}
