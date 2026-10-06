<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class CategoryRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    /**
     * @return array{id: int, slug: string, name: string, description: string}|null
     */
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT id, slug, name, description FROM categories WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);

        return $statement->fetch() ?: null;
    }

    /**
     * @return list<array{id: int, slug: string, name: string}>
     */
    public function findByArticle(int $articleId): array
    {
        $statement = $this->db->prepare(
            'SELECT c.id, c.slug, c.name
             FROM categories c
             JOIN article_category ac ON ac.category_id = c.id
             WHERE ac.article_id = :article_id
             ORDER BY c.name'
        );
        $statement->execute(['article_id' => $articleId]);

        return $statement->fetchAll();
    }

    /**
     * Categories that have at least one article.
     *
     * @return list<array{id: int, slug: string, name: string, description: string}>
     */
    public function findNotEmpty(): array
    {
        return $this->db->query(
            'SELECT c.id, c.slug, c.name, c.description
             FROM categories c
             WHERE EXISTS (SELECT 1 FROM article_category ac WHERE ac.category_id = c.id)
             ORDER BY c.name'
        )->fetchAll();
    }
}
