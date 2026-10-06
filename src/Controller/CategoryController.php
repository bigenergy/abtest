<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\NotFoundException;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\ArticleSort;
use App\Repository\CategoryRepository;

final class CategoryController
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ArticleRepository $articles,
        private readonly View $view,
    ) {
    }

    public function show(int $id): string
    {
        $category = $this->categories->find($id) ?? throw new NotFoundException();
        $sort = ArticleSort::fromQuery($_GET['sort'] ?? null);

        return $this->view->render('category.tpl', [
            'category' => $category,
            'articles' => $this->articles->findByCategory($id, $sort),
            'sort' => $sort,
            'sorts' => ArticleSort::cases(),
        ]);
    }
}
