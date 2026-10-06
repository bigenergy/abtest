<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Core\Database;
use App\Core\Slug;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Base class for tests that work with MySQL.
 *
 * Tables are recreated from database/schema.sql once per run, every test starts with them empty.
 */
abstract class DatabaseTestCase extends TestCase
{
    private static ?Database $database = null;

    protected Database $db;

    /** The same connection, for preparing test data */
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->db = self::database();
        $this->pdo = $this->db->pdo();

        $this->pdo->exec('DELETE FROM article_views');
        $this->pdo->exec('DELETE FROM article_category');
        $this->pdo->exec('DELETE FROM articles');
        $this->pdo->exec('DELETE FROM categories');
    }

    protected function createCategory(string $name): int
    {
        $this->pdo
            ->prepare('INSERT INTO categories (slug, name, description) VALUES (?, ?, ?)')
            ->execute([Slug::fromText($name), $name, "Описание категории {$name}"]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param list<int> $categoryIds
     */
    protected function createArticle(
        string $title,
        array $categoryIds,
        string $publishedAt = '2026-01-01 12:00:00',
        int $views = 0,
    ): int {
        $this->pdo
            ->prepare(
                'INSERT INTO articles (slug, title, description, content, image, views, published_at)
                 VALUES (?, ?, ?, ?, NULL, ?, ?)'
            )
            ->execute([
                Slug::fromText($title),
                $title,
                "Описание статьи {$title}",
                "Текст статьи {$title}",
                $views,
                $publishedAt,
            ]);
        $articleId = (int) $this->pdo->lastInsertId();

        $attach = $this->pdo->prepare('INSERT INTO article_category (article_id, category_id) VALUES (?, ?)');
        foreach ($categoryIds as $categoryId) {
            $attach->execute([$articleId, $categoryId]);
        }

        return $articleId;
    }

    private static function database(): Database
    {
        if (self::$database !== null) {
            return self::$database;
        }

        $root = dirname(__DIR__, 2);
        $config = (require $root . '/config/config.php')['db'];

        // The tables are dropped below, so the tests refuse to touch any other database
        if (!str_ends_with($config['name'], '_test')) {
            throw new RuntimeException("Integration tests need a database named *_test, got \"{$config['name']}\"");
        }

        $database = new Database($config);
        $database->pdo()->exec('DROP TABLE IF EXISTS article_views, article_category, articles, categories');

        $schema = file_get_contents($root . '/database/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            $database->pdo()->exec($statement);
        }

        return self::$database = $database;
    }
}
