<?php

declare(strict_types=1);

use App\Core\NotFoundException;
use App\Core\Router;
use App\Core\View;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$view = new View($root . '/templates', $root . '/var/templates_c');

$router = new Router();
$router->add('/', static fn (): string => $view->render('home.tpl'));

try {
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
