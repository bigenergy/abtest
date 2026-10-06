<?php

declare(strict_types=1);

namespace App\Tests\Core;

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
        $this->router->add('/article/{id}', static fn (int $id): string => "article {$id}");
    }

    public function testDispatchesExactPath(): void
    {
        self::assertSame('home', $this->router->dispatch('/'));
    }

    public function testPassesPlaceholderAsInteger(): void
    {
        self::assertSame('article 42', $this->router->dispatch('/article/42'));
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
        yield 'not a number' => ['/article/abc'];
        yield 'zero' => ['/article/0'];
        yield 'leading zero' => ['/article/01'];
        yield 'trailing slash' => ['/article/1/'];
        yield 'extra segment' => ['/article/1/edit'];
    }
}
