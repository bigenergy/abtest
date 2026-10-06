<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Categories that have at least one article.
     *
     * @return list<array{id: int, name: string, description: string}>
     */
    public function findNotEmpty(): array
    {
        return $this->pdo->query(
            'SELECT c.id, c.name, c.description
             FROM categories c
             WHERE EXISTS (SELECT 1 FROM article_category ac WHERE ac.category_id = c.id)
             ORDER BY c.name'
        )->fetchAll();
    }
}
