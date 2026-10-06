<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;

final class HomeController
{
    private const ARTICLES_PER_CATEGORY = 3;

    // The seeder clears the cache when articles change; the lifetime only limits
    // how long the view counters on the cards may lag behind
    private const CACHE_LIFETIME = 300;

    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ArticleRepository $articles,
        private readonly View $view,
    ) {
    }

    public function index(): string
    {
        return $this->view->renderCached('home.tpl', self::CACHE_LIFETIME, fn (): array => [
            'sections' => $this->sections(),
        ]);
    }

    /**
     * Categories that have articles, each with its latest ones.
     *
     * @return list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>
     */
    private function sections(): array
    {
        $latest = $this->articles->findLatestPerCategory(self::ARTICLES_PER_CATEGORY);

        $sections = [];
        foreach ($this->categories->findNotEmpty() as $category) {
            $sections[] = [
                'category' => $category,
                'articles' => $latest[$category['id']] ?? [],
            ];
        }

        return $sections;
    }
}
