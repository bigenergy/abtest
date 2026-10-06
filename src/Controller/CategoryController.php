<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\NotFoundException;
use App\Core\Paginator;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\ArticleSort;
use App\Repository\CategoryRepository;

final class CategoryController
{
    private const ARTICLES_PER_PAGE = 6;

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

        $paginator = Paginator::fromQuery(
            total: $this->articles->countByCategory($id),
            perPage: self::ARTICLES_PER_PAGE,
            page: $_GET['page'] ?? null,
            query: ['sort' => $sort->value],
        ) ?? throw new NotFoundException();

        return $this->view->render('category.tpl', [
            'category' => $category,
            'articles' => $this->articles->findByCategory($id, $sort, $paginator->perPage, $paginator->offset()),
            'sort' => $sort,
            'sorts' => ArticleSort::cases(),
            'paginator' => $paginator,
        ]);
    }
}
