<?php

declare(strict_types=1);

namespace App\Tests\Integration\Seeder;

use App\Seeder\Seeder;
use App\Tests\Integration\DatabaseTestCase;

final class SeederTest extends DatabaseTestCase
{
    /**
     * @var array{
     *     categories: list<array{name: string, description: string, topics: list<string>}>,
     *     titles: list<string>,
     *     descriptions: list<string>,
     *     sentences: list<string>,
     *     images: list<string>,
     * }
     */
    private array $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = require dirname(__DIR__, 3) . '/database/seed-data.php';
    }

    public function testCreatesCategoriesAndArticlesWithUniqueTitles(): void
    {
        (new Seeder($this->pdo, $this->data))->run(30);

        self::assertSame(count($this->data['categories']), $this->fetchCount('SELECT COUNT(*) FROM categories'));
        self::assertSame(30, $this->fetchCount('SELECT COUNT(*) FROM articles'));
        self::assertSame(30, $this->fetchCount('SELECT COUNT(DISTINCT title) FROM articles'));
    }

    public function testGivesEveryArticleOneToThreeCategories(): void
    {
        (new Seeder($this->pdo, $this->data))->run(30);

        $articlesWithWrongCount = $this->fetchCount(
            'SELECT COUNT(*) FROM articles a
             WHERE (SELECT COUNT(*) FROM article_category ac WHERE ac.article_id = a.id) NOT BETWEEN 1 AND 3'
        );

        self::assertSame(0, $articlesWithWrongCount);
    }

    public function testLeavesCategoriesWithoutTopicsEmpty(): void
    {
        $emptyCategories = array_filter($this->data['categories'], static fn (array $category): bool => !$category['topics']);
        self::assertNotEmpty($emptyCategories, 'Seed data is expected to have a category without articles');

        (new Seeder($this->pdo, $this->data))->run(30);

        $count = $this->pdo->prepare(
            'SELECT COUNT(*) FROM article_category ac JOIN categories c ON c.id = ac.category_id WHERE c.name = ?'
        );
        foreach ($emptyCategories as $category) {
            $count->execute([$category['name']]);
            self::assertSame(0, (int) $count->fetchColumn(), $category['name']);
        }
    }

    public function testReplacesPreviousData(): void
    {
        $seeder = new Seeder($this->pdo, $this->data);
        $seeder->run(10);
        $seeder->run(5);

        self::assertSame(count($this->data['categories']), $this->fetchCount('SELECT COUNT(*) FROM categories'));
        self::assertSame(5, $this->fetchCount('SELECT COUNT(*) FROM articles'));
    }

    private function fetchCount(string $sql): int
    {
        return (int) $this->pdo->query($sql)->fetchColumn();
    }
}
