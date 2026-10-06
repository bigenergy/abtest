<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use App\Core\NotFoundException;
use App\Core\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->add('/', static fn (): string => 'home');
        $this->router->add('/article/{slug}', static fn (string $slug): string => "article {$slug}");
    }

    public function testDispatchesExactPath(): void
    {
        self::assertSame('home', $this->router->dispatch('/'));
    }

    public function testPassesSlugToHandler(): void
    {
        self::assertSame('article indeksy-mysql-8', $this->router->dispatch('/article/indeksy-mysql-8'));
    }

    #[DataProvider('unknownPathProvider')]
    public function testThrowsNotFoundForUnknownPath(string $path): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->dispatch($path);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unknownPathProvider(): iterable
    {
        yield 'no such route' => ['/about'];
        yield 'empty slug' => ['/article/'];
        yield 'uppercase letters' => ['/article/Indeksy'];
        yield 'cyrillic letters' => ['/article/индексы'];
        yield 'double dash' => ['/article/a--b'];
        yield 'dash at the end' => ['/article/a-'];
        yield 'trailing slash' => ['/article/a/'];
        yield 'extra segment' => ['/article/a/edit'];
    }
}
