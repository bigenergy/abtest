<?php

declare(strict_types=1);

namespace App\Repository;

enum SortDirection: string
{
    case Desc = 'desc';
    case Asc = 'asc';

    /**
     * Newest and most viewed articles go first unless asked otherwise.
     */
    public static function fromQuery(mixed $value): self
    {
        return (is_string($value) ? self::tryFrom($value) : null) ?? self::Desc;
    }

    public function opposite(): self
    {
        return $this === self::Desc ? self::Asc : self::Desc;
    }

    public function label(): string
    {
        return match ($this) {
            self::Desc => 'по убыванию',
            self::Asc => 'по возрастанию',
        };
    }
}
