<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, callable> regular expression => handler */
    private array $routes = [];

    /**
     * Placeholders like {id} match positive integers and are passed to the handler as int.
     */
    public function add(string $pattern, callable $handler): void
    {
        $regex = '#^' . preg_replace('/\{\w+\}/', '([1-9]\d*)', $pattern) . '$#';

        $this->routes[$regex] = $handler;
    }

    /**
     * @throws NotFoundException
     */
    public function dispatch(string $path): string
    {
        foreach ($this->routes as $regex => $handler) {
            if (preg_match($regex, $path, $matches)) {
                $params = array_map('intval', array_slice($matches, 1));

                return $handler(...$params);
            }
        }

        throw new NotFoundException();
    }
}
