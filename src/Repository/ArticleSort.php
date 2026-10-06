<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * Sorting options of the article list: the field and the direction together.
 */
enum ArticleSort: string
{
    case Newest = 'date_desc';
    case Oldest = 'date_asc';
    case MostViewed = 'views_desc';
    case LeastViewed = 'views_asc';

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
            self::Newest => 'Сначала новые',
            self::Oldest => 'Сначала старые',
            self::MostViewed => 'Больше просмотров',
            self::LeastViewed => 'Меньше просмотров',
        };
    }
}
