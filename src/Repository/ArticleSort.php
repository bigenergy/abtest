<?php

declare(strict_types=1);

namespace App\Repository;

enum ArticleSort: string
{
    case Newest = 'date';
    case Popular = 'views';

    /**
     * Unknown or missing values fall back to the default sorting.
     */
    public static function fromQuery(mixed $value): self
    {
        return (is_string($value) ? self::tryFrom($value) : null) ?? self::Newest;
    }

    public function label(): string
    {
        return match ($this) {
            self::Newest => 'По дате',
            self::Popular => 'По просмотрам',
        };
    }
}
