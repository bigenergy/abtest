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
        $this->lastPage = max(1, (int) ceil($total / $perPage));
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
}
