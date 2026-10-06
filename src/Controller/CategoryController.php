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
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            ?: throw new NotFoundException();

        $paginator = new Paginator(
            total: $this->articles->countByCategory($id),
            perPage: self::ARTICLES_PER_PAGE,
            page: $page,
            query: ['sort' => $sort->value],
        );

        if ($page > $paginator->lastPage) {
            throw new NotFoundException();
        }

        return $this->view->render('category.tpl', [
            'category' => $category,
            'articles' => $this->articles->findByCategory($id, $sort, self::ARTICLES_PER_PAGE, $paginator->offset()),
            'sort' => $sort,
            'sorts' => ArticleSort::cases(),
            'paginator' => $paginator,
        ]);
    }
}
