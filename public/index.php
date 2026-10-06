<?php

declare(strict_types=1);

use App\Controller\ArticleController;
use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Core\Database;
use App\Core\NotFoundException;
use App\Core\Router;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\ViewCounter;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$config = require $root . '/config/config.php';
$view = new View($root . '/templates', $root . '/var/templates_c');

try {
    $pdo = Database::connect($config['db']);
    $categories = new CategoryRepository($pdo);
    $articles = new ArticleRepository($pdo);
    $viewCounter = new ViewCounter($articles);

    $router = new Router();
    $router->add('/', [new HomeController($categories, $articles, $view), 'index']);
    $router->add('/category/{slug}', [new CategoryController($categories, $articles, $view), 'show']);
    $router->add('/article/{slug}', [new ArticleController($categories, $articles, $viewCounter, $view), 'show']);

    echo $router->dispatch(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
} catch (NotFoundException) {
    http_response_code(404);
    echo $view->render('error.tpl', [
        'code' => 404,
        'title' => 'Страница не найдена',
        'text' => 'Возможно, её удалили или в ссылке опечатка.',
    ]);
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    echo $view->render('error.tpl', [
        'code' => 500,
        'title' => 'Что-то пошло не так',
        'text' => 'Попробуйте обновить страницу чуть позже.',
    ]);
}
