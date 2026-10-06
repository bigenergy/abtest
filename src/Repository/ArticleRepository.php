<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ArticleRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, title, description, content, image, views, published_at FROM articles WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function incrementViews(int $id): void
    {
        // The increment happens inside MySQL, so simultaneous views are not lost
        $this->pdo->prepare('UPDATE articles SET views = views + 1 WHERE id = :id')->execute(['id' => $id]);
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
        $statement = $this->pdo->prepare(
            'SELECT category_id, id, title, description, image, views, published_at
             FROM (
                 SELECT ac.category_id, a.id, a.title, a.description, a.image, a.views, a.published_at,
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
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM article_category WHERE category_id = :category_id');
        $statement->execute(['category_id' => $categoryId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findByCategory(
        int $categoryId,
        ArticleSort $sort,
        SortDirection $direction,
        int $limit,
        int $offset,
    ): array {
        // ORDER BY cannot be passed as a query parameter, so the column and the direction
        // are taken from fixed lists. The id makes the order stable for equal dates or views.
        $column = match ($sort) {
            ArticleSort::Date => 'a.published_at',
            ArticleSort::Views => 'a.views',
        };
        $order = match ($direction) {
            SortDirection::Desc => 'DESC',
            SortDirection::Asc => 'ASC',
        };

        $statement = $this->pdo->prepare(
            "SELECT a.id, a.title, a.description, a.image, a.views, a.published_at
             FROM articles a
             JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id
             ORDER BY {$column} {$order}, a.id {$order}
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
     *
     * @return list<array<string, mixed>>
     */
    public function findSimilar(int $articleId, int $limit): array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.id, a.title, a.description, a.image, a.views, a.published_at
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
}
