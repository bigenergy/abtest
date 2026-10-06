<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use App\Core\Slug;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    #[DataProvider('textProvider')]
    public function testMakesSlugFromText(string $text, string $expected): void
    {
        self::assertSame($expected, Slug::fromText($text));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function textProvider(): iterable
    {
        yield 'russian with punctuation' => ['Индексы MySQL: шпаргалка', 'indeksy-mysql-shpargalka'];
        yield 'quotes' => ['Связь «многие ко многим»', 'svyaz-mnogie-ko-mnogim'];
        yield 'digits with a dot' => ['Перечисления PHP 8.1', 'perechisleniya-php-8-1'];
        yield 'letters spelled with several latin ones' => ['Щука, жёлтый чай и юла', 'schuka-zheltyy-chay-i-yula'];
        yield 'hard and soft signs' => ['Подъезд и рельс', 'podezd-i-rels'];
        yield 'separators at the edges' => ['  — Docker / CI —  ', 'docker-ci'];
    }
}
