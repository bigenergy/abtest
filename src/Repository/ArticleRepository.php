<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

final class ArticleRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, slug, title, description, content, image, views, published_at
             FROM articles
             WHERE slug = :slug'
        );
        $statement->execute(['slug' => $slug]);

        return $statement->fetch() ?: null;
    }

    /**
     * Remembers that the visitor has seen the article today.
     * Returns false if they already have, so the view must not be counted again.
     *
     * @param string $visitor SHA-256 hash in hex
     */
    public function registerView(int $articleId, string $visitor): bool
    {
        // The primary key allows one row per visitor and day, INSERT IGNORE skips a repeated one
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO article_views (article_id, visitor, viewed_on)
             VALUES (:article_id, UNHEX(:visitor), CURDATE())'
        );
        $statement->execute(['article_id' => $articleId, 'visitor' => $visitor]);

        return $statement->rowCount() === 1;
    }

    public function incrementViews(int $id): void
    {
        // The increment happens inside MySQL, so simultaneous views are not lost
        $this->db->prepare('UPDATE articles SET views = views + 1 WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * The latest articles of every category, grouped by category id.
     *
     * @return array<int, list<array<string, mixed>>>
     */
    public function findLatestPerCategory(int $limit): array
    {
        // ROW_NUMBER() numbers articles inside each category starting from the newest one,
        // so one query returns the top N for all categories at once
        $statement = $this->db->prepare(
            'SELECT category_id, id, slug, title, description, image, views, published_at
             FROM (
                 SELECT ac.category_id, a.id, a.slug, a.title, a.description, a.image, a.views, a.published_at,
                        ROW_NUMBER() OVER (
                            PARTITION BY ac.category_id
                            ORDER BY a.published_at DESC, a.id DESC
                        ) AS row_num
                 FROM article_category ac
                 JOIN articles a ON a.id = ac.article_id
             ) AS ranked
             WHERE row_num <= :limit
             ORDER BY category_id, row_num'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $grouped = [];
        foreach ($statement->fetchAll() as $article) {
            $grouped[$article['category_id']][] = $article;
        }

        return $grouped;
    }

    public function countByCategory(int $categoryId): int
    {
        // No join with articles is needed: the foreign key guarantees that every link points to an article
        $statement = $this->db->prepare('SELECT COUNT(*) FROM article_category WHERE category_id = :category_id');
        $statement->execute(['category_id' => $categoryId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findByCategory(int $categoryId, ArticleSort $sort, int $limit, int $offset): array
    {
        // ORDER BY cannot be passed as a query parameter, so it comes from a fixed list.
        // The id makes the order stable for articles with equal dates or views.
        $orderBy = match ($sort) {
            ArticleSort::Newest => 'a.published_at DESC, a.id DESC',
            ArticleSort::Oldest => 'a.published_at ASC, a.id ASC',
            ArticleSort::MostViewed => 'a.views DESC, a.id DESC',
            ArticleSort::LeastViewed => 'a.views ASC, a.id ASC',
        };

        $statement = $this->db->prepare(
            "SELECT a.id, a.slug, a.title, a.description, a.image, a.views, a.published_at
             FROM articles a
             JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id
             ORDER BY {$orderBy}
             LIMIT :limit OFFSET :offset"
        );
        $statement->bindValue('category_id', $categoryId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Articles that share the most categories with the given one; fresher ones go first among equals.
     * If there are not enough of them, the list is filled up with the newest articles.
     *
     * @return list<array<string, mixed>>
     */
    public function findSimilar(int $articleId, int $limit): array
    {
        $similar = $this->findBySharedCategories($articleId, $limit);

        $missing = $limit - count($similar);
        if ($missing > 0) {
            $excludeIds = [$articleId, ...array_column($similar, 'id')];
            $similar = [...$similar, ...$this->findLatestExcept($excludeIds, $missing)];
        }

        return $similar;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findBySharedCategories(int $articleId, int $limit): array
    {
        $statement = $this->db->prepare(
            'SELECT a.id, a.slug, a.title, a.description, a.image, a.views, a.published_at
             FROM article_category ac
             JOIN article_category related
                  ON related.category_id = ac.category_id
                 AND related.article_id <> ac.article_id
             JOIN articles a ON a.id = related.article_id
             WHERE ac.article_id = :article_id
             GROUP BY a.id
             ORDER BY COUNT(*) DESC, a.published_at DESC, a.id DESC
             LIMIT :limit'
        );
        $statement->bindValue('article_id', $articleId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @param list<int> $excludeIds
     * @return list<array<string, mixed>>
     */
    private function findLatestExcept(array $excludeIds, int $limit): array
    {
        // One placeholder per excluded id: NOT IN (?, ?, ?)
        $placeholders = implode(', ', array_fill(0, count($excludeIds), '?'));

        $statement = $this->db->prepare(
            "SELECT id, slug, title, description, image, views, published_at
             FROM articles
             WHERE id NOT IN ({$placeholders})
             ORDER BY published_at DESC, id DESC
             LIMIT ?"
        );
        foreach ([...$excludeIds, $limit] as $index => $value) {
            $statement->bindValue($index + 1, $value, PDO::PARAM_INT);
        }
        $statement->execute();

        return $statement->fetchAll();
    }
}
