<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Repository\ArticleRepository;
use App\Service\ViewCounter;
use App\Tests\Integration\DatabaseTestCase;

final class ViewCounterTest extends DatabaseTestCase
{
    private ArticleRepository $articles;
    private ViewCounter $counter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articles = new ArticleRepository($this->pdo);
        $this->counter = new ViewCounter($this->articles);

        // Every test is a new visitor with a regular browser
        $_SESSION = [];
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/131.0';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testCountsArticleOncePerSession(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $this->counter->count($id);
        $this->counter->count($id);

        self::assertSame(1, $this->views('statya'));
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

    public function testCountsAgainInNewSession(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $this->counter->count($id);
        $_SESSION = [];
        $this->counter->count($id);

        self::assertSame(2, $this->views('statya'));
    }

    public function testIgnoresRobots(): void
    {
        $id = $this->createArticle('Статья', [$this->createCategory('PHP')]);

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $this->counter->count($id);
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->counter->count($id);

        self::assertSame(0, $this->views('statya'));
    }

    private function views(string $slug): int
    {
        return $this->articles->findBySlug($slug)['views'] ?? -1;
    }
}
