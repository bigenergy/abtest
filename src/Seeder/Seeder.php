<?php

declare(strict_types=1);

namespace App\Seeder;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Recreates demo data: categories and generated articles.
 * Texts are assembled from the pieces in database/seed-data.php.
 */
final class Seeder
{
    /**
     * @param array{
     *     categories: list<array{name: string, description: string, topics: list<string>}>,
     *     titles: list<string>,
     *     descriptions: list<string>,
     *     sentences: list<string>,
     *     images: list<string>,
     * } $data
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $data,
    ) {
    }

    public function run(int $articleCount): void
    {
        $this->truncateTables();

        $this->pdo->beginTransaction();
        try {
            $topics = $this->seedCategories();
            $this->seedArticles($articleCount, $topics);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function truncateTables(): void
    {
        // MySQL does not allow TRUNCATE of a table referenced by a foreign key while the checks are on
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->pdo->exec('TRUNCATE TABLE article_category');
        $this->pdo->exec('TRUNCATE TABLE articles');
        $this->pdo->exec('TRUNCATE TABLE categories');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @return array<int, list<string>> category id => topics for article titles
     */
    private function seedCategories(): array
    {
        $insert = $this->pdo->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');

        $topics = [];
        foreach ($this->data['categories'] as $category) {
            $insert->execute(['name' => $category['name'], 'description' => $category['description']]);
            $topics[(int) $this->pdo->lastInsertId()] = $category['topics'];
        }

        return $topics;
    }

    /**
     * @param array<int, list<string>> $topics
     */
    private function seedArticles(int $count, array $topics): void
    {
        // Categories without topics get no articles
        $topics = array_filter($topics);
        $categoryIds = array_keys($topics);
        $titles = array_map($this->shuffledTitles(...), $topics);

        $insertArticle = $this->pdo->prepare(
            'INSERT INTO articles (title, description, content, image, views, published_at)
             VALUES (:title, :description, :content, :image, :views, :published_at)'
        );
        $attachCategory = $this->pdo->prepare(
            'INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)'
        );

        for ($i = 0; $i < $count; $i++) {
            // The main category is chosen in turn, so articles are spread evenly
            $mainCategoryId = $categoryIds[$i % count($categoryIds)];
            [$topic, $title] = array_pop($titles[$mainCategoryId])
                ?? throw new RuntimeException('Not enough unique titles: add topics to database/seed-data.php');

            $insertArticle->execute([
                'title' => $title,
                'description' => sprintf($this->randomItem($this->data['descriptions']), $topic),
                'content' => $this->makeContent(),
                'image' => $this->randomItem($this->data['images']),
                'views' => random_int(0, 5000),
                'published_at' => date('Y-m-d H:i:s', time() - random_int(0, 365 * 24 * 3600)),
            ]);
            $articleId = (int) $this->pdo->lastInsertId();

            $extraCategoryIds = $this->randomItems(array_diff($categoryIds, [$mainCategoryId]), random_int(0, 2));
            foreach ([$mainCategoryId, ...$extraCategoryIds] as $categoryId) {
                $attachCategory->execute(['article_id' => $articleId, 'category_id' => $categoryId]);
            }
        }
    }

    /**
     * Every "topic + title template" combination in random order.
     *
     * @param list<string> $topics
     * @return list<array{string, string}> pairs of topic and title
     */
    private function shuffledTitles(array $topics): array
    {
        $titles = [];
        foreach ($topics as $topic) {
            foreach ($this->data['titles'] as $template) {
                $titles[] = [$topic, sprintf($template, $topic)];
            }
        }
        shuffle($titles);

        return $titles;
    }

    /**
     * A few paragraphs separated by an empty line; sentences are not repeated within one text.
     */
    private function makeContent(): string
    {
        $sentences = $this->data['sentences'];
        shuffle($sentences);

        $paragraphs = [];
        for ($i = random_int(4, 6); $i > 0; $i--) {
            $paragraphs[] = implode(' ', array_splice($sentences, 0, random_int(3, 5)));
        }

        return implode("\n\n", $paragraphs);
    }

    /**
     * @template T
     * @param array<T> $items
     * @return T
     */
    private function randomItem(array $items): mixed
    {
        return $items[array_rand($items)];
    }

    /**
     * @template T
     * @param array<T> $items
     * @return list<T>
     */
    private function randomItems(array $items, int $count): array
    {
        shuffle($items);

        return array_slice($items, 0, $count);
    }
}
