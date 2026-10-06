<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * MySQL connection that is opened on the first query.
 * A page served from cache makes no queries, so it does not connect at all.
 */
final class Database
{
    private ?PDO $pdo = null;

    /**
     * @param array{host: string, port: int, name: string, user: string, password: string} $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo()->prepare($sql);
    }

    public function query(string $sql): PDOStatement
    {
        return $this->pdo()->query($sql);
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $this->config['host'],
                $this->config['port'],
                $this->config['name'],
            );

            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Native MySQL prepared statements instead of emulating them inside PDO
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $this->pdo;
    }
}
