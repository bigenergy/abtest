<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;
use Smarty\Smarty;

final class View
{
    private const MONTHS = [
        1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
    ];

    private readonly Smarty $smarty;

    public function __construct(string $templateDir, string $compileDir, string $cacheDir)
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($templateDir);
        $this->smarty->setCompileDir($compileDir);
        $this->smarty->setCacheDir($cacheDir);
        // Every {$variable} is escaped, so data from the database is safe to print as is
        $this->smarty->setEscapeHtml(true);
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'date_ru', self::formatDate(...));
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'paragraphs', self::splitParagraphs(...));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $tpl = $this->smarty->createTemplate($template);
        $tpl->assign($data);

        return $tpl->fetch();
    }

    /**
     * Renders a template and keeps the result for $lifetime seconds.
     * The data callback runs only when there is no fresh copy, so its queries are skipped as well.
     *
     * @param callable(): array<string, mixed> $data
     */
    public function renderCached(string $template, int $lifetime, callable $data): string
    {
        $tpl = $this->smarty->createTemplate($template);
        $tpl->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
        $tpl->setCacheLifetime($lifetime);

        if (!$tpl->isCached()) {
            $tpl->assign($data());
        }

        return $tpl->fetch();
    }

    public function clearCache(): void
    {
        $this->smarty->clearAllCache();
    }

    /**
     * "2026-03-08 14:30:00" => "8 марта 2026"
     */
    private static function formatDate(string $value): string
    {
        $date = new DateTimeImmutable($value);

        return sprintf('%d %s %d', $date->format('j'), self::MONTHS[(int) $date->format('n')], $date->format('Y'));
    }

    /**
     * Plain text is stored with paragraphs separated by an empty line.
     *
     * @return list<string>
     */
    private static function splitParagraphs(string $text): array
    {
        return preg_split('/\R\s*\R/', trim($text));
    }
}
