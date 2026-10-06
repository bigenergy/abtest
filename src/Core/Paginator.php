<?php

declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    public readonly int $lastPage;

    /**
     * @param array<string, string> $query parameters to keep in page links, e.g. the sorting
     */
    public function __construct(
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $page,
        private readonly array $query = [],
    ) {
        $this->lastPage = self::countPages($total, $perPage);
    }

    /**
     * Takes the page number as it came in the query string.
     * Returns null if it is not a number or there is no such page.
     *
     * @param array<string, string> $query
     */
    public static function fromQuery(int $total, int $perPage, mixed $page, array $query = []): ?self
    {
        $page = filter_var($page ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => self::countPages($total, $perPage)],
        ]);

        return $page === false ? null : new self($total, $perPage, $page, $query);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->lastPage;
    }

    public function url(int $page): string
    {
        return '?' . http_build_query([...$this->query, 'page' => $page]);
    }

    /**
     * Page numbers to link to: the first, the last and a few around the current one.
     * Null stands for skipped pages and is shown as "…".
     *
     * @return list<int|null>
     */
    public function links(int $around = 2): array
    {
        $links = [];
        for ($page = 1; $page <= $this->lastPage; $page++) {
            if ($page === 1 || $page === $this->lastPage || abs($page - $this->page) <= $around) {
                $links[] = $page;
            } elseif (end($links) !== null) {
                $links[] = null;
            }
        }

        return $links;
    }

    private static function countPages(int $total, int $perPage): int
    {
        // An empty list still has one (empty) page
        return max(1, (int) ceil($total / $perPage));
    }
}
