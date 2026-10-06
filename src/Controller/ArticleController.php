<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\NotFoundException;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\ViewCounter;

final class ArticleController
{
    private const SIMILAR_ARTICLES = 3;

    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ArticleRepository $articles,
        private readonly ViewCounter $viewCounter,
        private readonly View $view,
    ) {
    }

    public function show(string $slug): string
    {
        $article = $this->articles->findBySlug($slug) ?? throw new NotFoundException();
        $this->viewCounter->count($article['id']);

        return $this->view->render('article.tpl', [
            'article' => $article,
            'categories' => $this->categories->findByArticle($article['id']),
            'similar' => $this->articles->findSimilar($article['id'], self::SIMILAR_ARTICLES),
        ]);
    }
}
