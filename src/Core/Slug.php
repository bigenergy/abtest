<?php

declare(strict_types=1);

namespace App\Core;

final class Slug
{
    private const CYRILLIC_TO_LATIN = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    /**
     * Makes a URL part from a title: "Индексы MySQL: шпаргалка" => "indeksy-mysql-shpargalka".
     */
    public static function fromText(string $text): string
    {
        $latin = strtr(mb_strtolower($text), self::CYRILLIC_TO_LATIN);

        return trim(preg_replace('/[^a-z0-9]+/', '-', $latin), '-');
    }
}
