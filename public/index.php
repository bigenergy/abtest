<?php

declare(strict_types=1);

use App\Core\NotFoundException;
use App\Core\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();
$router->add('/', static fn (): string => 'Главная страница');

try {
    echo $router->dispatch(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
} catch (NotFoundException) {
    http_response_code(404);
    echo 'Страница не найдена';
}
