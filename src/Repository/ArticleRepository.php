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
}
