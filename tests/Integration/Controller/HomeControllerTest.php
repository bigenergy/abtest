<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\HomeController;
use App\Core\Database;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Tests\Integration\DatabaseTestCase;

final class HomeControllerTest extends DatabaseTestCase
{
    private View $view;

    protected function setUp(): void
    {
        parent::setUp();

        $root = dirname(__DIR__, 3);
        $this->view = new View($root . '/templates', $root . '/var/test/templates_c', $root . '/var/test/cache');
        $this->view->clearCache();
    }

    public function testServesCachedPageWithoutTouchingDatabase(): void
    {
        $this->createArticle('Статья для главной', [$this->createCategory('PHP')]);
        $page = $this->controller($this->db)->index();

        // Nothing listens on port 1: any query through this connection would fail
        $unreachable = new Database(['host' => '127.0.0.1', 'port' => 1, 'name' => 'blog', 'user' => 'blog', 'password' => '']);
        $cachedPage = $this->controller($unreachable)->index();

        self::assertStringContainsString('Статья для главной', $page);
        self::assertSame($page, $cachedPage);
    }

    private function controller(Database $database): HomeController
    {
        return new HomeController(new CategoryRepository($database), new ArticleRepository($database), $this->view);
    }
}
