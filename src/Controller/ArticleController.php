<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\NotFoundException;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;

final class ArticleController
{
    private const SIMILAR_ARTICLES = 3;

    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ArticleRepository $articles,
        private readonly View $view,
    ) {
    }

    public function show(int $id): string
    {
        $article = $this->articles->find($id) ?? throw new NotFoundException();
        $this->articles->incrementViews($id);

        return $this->view->render('article.tpl', [
            'article' => $article,
            'categories' => $this->categories->findByArticle($id),
            'similar' => $this->articles->findSimilar($id, self::SIMILAR_ARTICLES),
        ]);
    }
}
