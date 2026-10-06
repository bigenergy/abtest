<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\CategoryRepository;
use App\Tests\Integration\DatabaseTestCase;

final class CategoryRepositoryTest extends DatabaseTestCase
{
    private CategoryRepository $categories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categories = new CategoryRepository($this->pdo);
    }

    public function testFindsCategoryById(): void
    {
        $id = $this->createCategory('PHP');

        $category = $this->categories->find($id);

        self::assertNotNull($category);
        self::assertSame('PHP', $category['name']);
        self::assertNull($this->categories->find($id + 1));
    }

    public function testSkipsCategoriesWithoutArticles(): void
    {
        $sql = $this->createCategory('SQL');
        $this->createCategory('Пустая');
        $php = $this->createCategory('PHP');
        $this->createArticle('Статья', [$php, $sql]);

        self::assertSame(['PHP', 'SQL'], array_column($this->categories->findNotEmpty(), 'name'));
    }

    public function testFindsAllCategoriesOfArticle(): void
    {
        $sql = $this->createCategory('SQL');
        $php = $this->createCategory('PHP');
        $this->createCategory('DevOps');
        $article = $this->createArticle('Статья', [$sql, $php]);

        self::assertSame(['PHP', 'SQL'], array_column($this->categories->findByArticle($article), 'name'));
    }
}
