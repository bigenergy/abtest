<?php

declare(strict_types=1);

namespace App\Repository;

enum ArticleSort: string
{
    case Date = 'date';
    case Views = 'views';

    /**
     * Unknown or missing values fall back to the default sorting.
     */
    public static function fromQuery(mixed $value): self
    {
        return (is_string($value) ? self::tryFrom($value) : null) ?? self::Date;
    }

    public function label(): string
    {
        return match ($this) {
            self::Date => 'По дате',
            self::Views => 'По просмотрам',
        };
    }
}
