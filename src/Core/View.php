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

    public function __construct(string $templateDir, string $compileDir)
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($templateDir);
        $this->smarty->setCompileDir($compileDir);
        // Every {$variable} is escaped, so data from the database is safe to print as is
        $this->smarty->setEscapeHtml(true);
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'date_ru', self::formatDate(...));
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
     * "2026-03-08 14:30:00" => "8 марта 2026"
     */
    private static function formatDate(string $date): string
    {
        $date = new DateTimeImmutable($date);

        return sprintf('%d %s %d', $date->format('j'), self::MONTHS[(int) $date->format('n')], $date->format('Y'));
    }
}
