<?php

declare(strict_types=1);

namespace App\Tests\Core;

use App\Core\View;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    private View $view;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);
        $this->view = new View($root . '/templates', $root . '/var/test/templates_c');
    }

    public function testFormatsDateInRussian(): void
    {
        $html = $this->view->render('string:{$date|date_ru}', ['date' => '2026-03-08 14:30:00']);

        self::assertSame('8 марта 2026', $html);
    }

    public function testSplitsTextIntoParagraphsByEmptyLines(): void
    {
        $html = $this->view->render(
            'string:{foreach $text|paragraphs as $paragraph}<p>{$paragraph}</p>{/foreach}',
            ['text' => "First line\nstill first\n\nSecond\r\n\r\nThird\n"],
        );

        self::assertSame("<p>First line\nstill first</p><p>Second</p><p>Third</p>", $html);
    }

    public function testEscapesVariables(): void
    {
        $html = $this->view->render('string:{$title}', ['title' => '<script>alert(1)</script>']);

        self::assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testRendersErrorPageWithinLayout(): void
    {
        $html = $this->view->render('error.tpl', [
            'code' => 404,
            'title' => 'Страница не найдена',
            'text' => 'Проверьте адрес.',
        ]);

        self::assertStringContainsString('<title>Страница не найдена — DevNotes</title>', $html);
        self::assertStringContainsString('<p class="error-page__code">404</p>', $html);
    }
}
