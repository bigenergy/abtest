<?php

declare(strict_types=1);

$env = static function (string $name, string $default): string {
    $value = getenv($name);

    return $value === false ? $default : $value;
};

return [
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => (int) $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'blog'),
        'user' => $env('DB_USER', 'blog'),
        'password' => $env('DB_PASSWORD', 'secret'),
    ],
];
