<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Repository\ArticleRepository;
use App\Service\ViewCounter;
use App\Tests\Integration\DatabaseTestCase;

final class ViewCounterTest extends DatabaseTestCase
{
    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/131.0';

    private ArticleRepository $articles;
    private ViewCounter $counter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articles = new ArticleRepository($this->db);
        $this->counter = new ViewCounter($this->articles);

        $this->visitFrom('203.0.113.10', self::BROWSER);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    }

    public function testCountsVisitorOnceADay(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $this->counter->count($id);
        $this->counter->count($id);

        self::assertSame(1, $this->views('statya'));
    }

    public function testCountsAgainNextDay(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $this->counter->count($id);
        // As if the first view happened yesterday
        $this->pdo->exec('UPDATE article_views SET viewed_on = CURDATE() - INTERVAL 1 DAY');
        $this->counter->count($id);

        self::assertSame(2, $this->views('statya'));
    }

    public function testTellsVisitorsApartByAddressAndBrowser(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $this->counter->count($id);
        $this->visitFrom('203.0.113.20', self::BROWSER);
        $this->counter->count($id);
        $this->visitFrom('203.0.113.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Safari/604.1');
        $this->counter->count($id);

        self::assertSame(3, $this->views('statya'));
    }

    public function testCountsDifferentArticlesSeparately(): void
    {
        $php = $this->createCategory('PHP');
        $first = $this->createArticle('Первая', [$php]);
        $second = $this->createArticle('Вторая', [$php]);

        $this->counter->count($first);
        $this->counter->count($second);

        self::assertSame(1, $this->views('pervaya'));
        self::assertSame(1, $this->views('vtoraya'));
    }

    public function testIgnoresRobots(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $this->visitFrom('66.249.66.1', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $this->counter->count($id);
        $this->visitFrom('198.51.100.7', '');
        $this->counter->count($id);

        self::assertSame(0, $this->views('statya'));
    }

    private function visitFrom(string $address, string $userAgent): void
    {
        $_SERVER['REMOTE_ADDR'] = $address;
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
    }

    private function views(string $slug): int
    {
        return $this->articles->findBySlug($slug)['views'] ?? -1;
    }
}
