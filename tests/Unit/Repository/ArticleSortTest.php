<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Repository\ArticleSort;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArticleSortTest extends TestCase
{
    public function testParsesKnownValue(): void
    {
        self::assertSame(ArticleSort::LeastViewed, ArticleSort::fromQuery('views_asc'));
    }

    #[DataProvider('unknownValueProvider')]
    public function testFallsBackToNewestFirst(mixed $value): void
    {
        self::assertSame(ArticleSort::Newest, ArticleSort::fromQuery($value));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function unknownValueProvider(): iterable
    {
        yield 'missing' => [null];
        yield 'unknown option' => ['rating'];
        yield 'different case' => ['DATE_DESC'];
        yield 'array' => [['views_asc']];
    }

    public function testEveryOptionHasLabel(): void
    {
        foreach (ArticleSort::cases() as $sort) {
            self::assertNotSame('', $sort->label(), $sort->name);
        }
    }
}
