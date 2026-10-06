<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;

final class HomeController
{
    private const ARTICLES_PER_CATEGORY = 3;

    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ArticleRepository $articles,
        private readonly View $view,
    ) {
    }

    public function index(): string
    {
        $latest = $this->articles->findLatestPerCategory(self::ARTICLES_PER_CATEGORY);

        $sections = [];
        foreach ($this->categories->findNotEmpty() as $category) {
            $sections[] = [
                'category' => $category,
                'articles' => $latest[$category['id']] ?? [],
            ];
        }

        return $this->view->render('home.tpl', ['sections' => $sections]);
    }
}
