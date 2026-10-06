<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\ArticleRepository;
use App\Repository\ArticleSort;
use App\Tests\Integration\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ArticleRepositoryTest extends DatabaseTestCase
{
    private ArticleRepository $articles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articles = new ArticleRepository($this->db);
    }

    public function testFindsArticleBySlug(): void
    {
        $this->createArticle('Генераторы PHP', [$this->createCategory('PHP')]);

        $article = $this->articles->findBySlug('generatory-php');

        self::assertNotNull($article);
        self::assertSame('Генераторы PHP', $article['title']);
        self::assertNull($this->articles->findBySlug('net-takoy'));
    }

    public function testIncrementsViews(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')], views: 10);

        $this->articles->incrementViews($id);
        $this->articles->incrementViews($id);

        self::assertSame(12, $this->articles->findBySlug('statya')['views'] ?? null);
    }

    public function testTakesThreeNewestArticlesOfEachCategory(): void
    {
        $php = $this->createCategory('PHP');
        $sql = $this->createCategory('SQL');
        $this->createCategory('Пустая');

        $this->createArticle('Самая старая', [$php], '2026-01-01 10:00:00');
        $second = $this->createArticle('Вторая', [$php], '2026-02-01 10:00:00');
        $third = $this->createArticle('Третья', [$php, $sql], '2026-03-01 10:00:00');
        $newest = $this->createArticle('Самая новая', [$php], '2026-04-01 10:00:00');

        $latest = $this->articles->findLatestPerCategory(3);

        self::assertSame([$php, $sql], array_keys($latest), 'A category without articles is skipped');
        self::assertSame([$newest, $third, $second], array_column($latest[$php], 'id'));
        self::assertSame([$third], array_column($latest[$sql], 'id'));
    }

    /**
     * @param list<string> $expectedTitles
     */
    #[DataProvider('sortProvider')]
    public function testSortsArticlesOfCategory(ArticleSort $sort, array $expectedTitles): void
    {
        $php = $this->createCategory('PHP');
        $this->createArticle('A', [$php], '2026-01-01 10:00:00', views: 50);
        $this->createArticle('B', [$php], '2026-02-01 10:00:00', views: 10);
        $this->createArticle('C', [$php], '2026-03-01 10:00:00', views: 30);
        $this->createArticle('Из другой категории', [$this->createCategory('SQL')], '2026-04-01 10:00:00', views: 99);

        $articles = $this->articles->findByCategory($php, $sort, limit: 10, offset: 0);

        self::assertSame($expectedTitles, array_column($articles, 'title'));
    }

    /**
     * @return iterable<string, array{ArticleSort, list<string>}>
     */
    public static function sortProvider(): iterable
    {
        yield 'newest first' => [ArticleSort::Newest, ['C', 'B', 'A']];
        yield 'oldest first' => [ArticleSort::Oldest, ['A', 'B', 'C']];
        yield 'most viewed first' => [ArticleSort::MostViewed, ['A', 'C', 'B']];
        yield 'least viewed first' => [ArticleSort::LeastViewed, ['B', 'C', 'A']];
    }

    public function testPagesKeepStableOrderForEqualDates(): void
    {
        $php = $this->createCategory('PHP');
        $ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $ids[] = $this->createArticle("Статья {$i}", [$php], '2026-01-01 10:00:00');
        }

        $pages = [];
        foreach ([0, 2, 4] as $offset) {
            $pages[] = $this->articles->findByCategory($php, ArticleSort::Newest, limit: 2, offset: $offset);
        }

        // With equal dates the id decides, so every article appears exactly once
        self::assertSame(array_reverse($ids), array_column(array_merge(...$pages), 'id'));
        self::assertSame(5, $this->articles->countByCategory($php));
    }

    public function testSimilarArticlesShareMostCategories(): void
    {
        $php = $this->createCategory('PHP');
        $sql = $this->createCategory('SQL');

        $article = $this->createArticle('Текущая', [$php, $sql], '2026-01-01 10:00:00');
        $bothCategories = $this->createArticle('Обе категории', [$php, $sql], '2026-01-02 10:00:00');
        $oneCategory = $this->createArticle('Одна категория', [$sql], '2026-01-03 10:00:00');
        $this->createArticle('Без общих категорий', [$this->createCategory('DevOps')], '2026-01-04 10:00:00');

        $similar = $this->articles->findSimilar($article, 2);

        self::assertSame([$bothCategories, $oneCategory], array_column($similar, 'id'));
    }

    public function testFillsUpSimilarArticlesWithNewest(): void
    {
        $php = $this->createCategory('PHP');
        $mobile = $this->createCategory('Мобильная разработка');

        // Newest of all, but must not recommend itself
        $article = $this->createArticle('Текущая', [$mobile], '2026-06-01 10:00:00');
        $neighbour = $this->createArticle('Соседка по категории', [$mobile], '2024-01-01 10:00:00');
        $newest = $this->createArticle('Новая', [$php], '2026-03-01 10:00:00');
        $newer = $this->createArticle('Поновее', [$php], '2026-02-01 10:00:00');
        $this->createArticle('Старая', [$php], '2025-01-01 10:00:00');

        $similar = $this->articles->findSimilar($article, 3);

        self::assertSame([$neighbour, $newest, $newer], array_column($similar, 'id'));
    }
}
