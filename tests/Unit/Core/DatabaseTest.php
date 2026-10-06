<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use App\Core\Database;
use PDOException;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function testConnectsOnlyOnFirstUse(): void
    {
        // Nothing listens on port 1, so connecting is bound to fail
        $database = new Database(['host' => '127.0.0.1', 'port' => 1, 'name' => 'blog', 'user' => 'blog', 'password' => '']);

        $this->expectException(PDOException::class);

        $database->pdo();
    }
}
