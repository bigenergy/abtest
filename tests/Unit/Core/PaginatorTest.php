<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use App\Core\Paginator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testCountsPagesAndOffset(): void
    {
        $paginator = new Paginator(total: 17, perPage: 6, page: 3);

        self::assertSame(3, $paginator->lastPage);
        self::assertSame(12, $paginator->offset());
        self::assertTrue($paginator->hasPrevious());
        self::assertFalse($paginator->hasNext());
    }

    public function testEmptyListHasOnePage(): void
    {
        $paginator = new Paginator(total: 0, perPage: 6, page: 1);

        self::assertSame(1, $paginator->lastPage);
        self::assertSame(0, $paginator->offset());
        self::assertFalse($paginator->hasPrevious());
        self::assertFalse($paginator->hasNext());
    }

    public function testUrlKeepsQueryParameters(): void
    {
        $paginator = new Paginator(total: 30, perPage: 6, page: 1, query: ['sort' => 'views_desc']);

        self::assertSame('?sort=views_desc&page=2', $paginator->url(2));
    }

    public function testLinksToEveryPageWhenThereAreFew(): void
    {
        $paginator = new Paginator(total: 20, perPage: 6, page: 2);

        self::assertSame([1, 2, 3, 4], $paginator->links());
    }

    /**
     * @param list<int|null> $expected
     */
    #[DataProvider('manyPagesProvider')]
    public function testCollapsesDistantPagesIntoGaps(int $page, array $expected): void
    {
        $paginator = new Paginator(total: 60, perPage: 6, page: $page);

        self::assertSame($expected, $paginator->links());
    }

    /**
     * Ten pages; null stands for "…".
     *
     * @return iterable<string, array{int, list<int|null>}>
     */
    public static function manyPagesProvider(): iterable
    {
        yield 'first page' => [1, [1, 2, 3, null, 10]];
        yield 'middle page' => [5, [1, null, 3, 4, 5, 6, 7, null, 10]];
        yield 'last page' => [10, [1, null, 8, 9, 10]];
    }

    #[DataProvider('existingPageProvider')]
    public function testFromQueryAcceptsExistingPage(mixed $page, int $expected): void
    {
        $paginator = Paginator::fromQuery(total: 17, perPage: 6, page: $page);

        self::assertNotNull($paginator);
        self::assertSame($expected, $paginator->page);
    }

    /**
     * @return iterable<string, array{string|null, int}>
     */
    public static function existingPageProvider(): iterable
    {
        yield 'no page in the query' => [null, 1];
        yield 'first page' => ['1', 1];
        yield 'last page' => ['3', 3];
    }

    #[DataProvider('wrongPageProvider')]
    public function testFromQueryRejectsWrongPage(mixed $page): void
    {
        self::assertNull(Paginator::fromQuery(total: 17, perPage: 6, page: $page));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function wrongPageProvider(): iterable
    {
        yield 'after the last page' => ['4'];
        yield 'zero' => ['0'];
        yield 'negative' => ['-1'];
        yield 'not a number' => ['abc'];
        yield 'fraction' => ['1.5'];
        yield 'empty string' => [''];
        yield 'array' => [['2']];
    }

    public function testFromQueryAllowsOnlyFirstPageOfEmptyList(): void
    {
        self::assertNotNull(Paginator::fromQuery(total: 0, perPage: 6, page: null));
        self::assertNull(Paginator::fromQuery(total: 0, perPage: 6, page: '2'));
    }
}
