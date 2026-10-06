<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    /**
     * @param array{host: string, port: int, name: string, user: string, password: string} $config
     */
    public static function connect(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['name'],
        );

        return new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Native MySQL prepared statements instead of emulating them inside PDO
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
