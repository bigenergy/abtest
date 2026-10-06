<?php

/*
 * Fills the database with demo categories and articles. Existing data is removed.
 *
 * Usage: php bin/seed.php [--articles=60]
 */

declare(strict_types=1);

use App\Core\Database;
use App\Core\View;
use App\Seeder\Seeder;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$config = require $root . '/config/config.php';

$options = getopt('', ['articles:']);
$articleCount = filter_var($options['articles'] ?? 60, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 500],
]);

if ($articleCount === false) {
    fwrite(STDERR, "Количество статей должно быть числом от 1 до 500\n");
    exit(1);
}

$database = new Database($config['db']);
$seeder = new Seeder($database->pdo(), require $root . '/database/seed-data.php');
$seeder->run($articleCount);

// The cached home page still lists the old articles
$view = new View($root . '/templates', $root . '/var/templates_c', $root . '/var/cache');
$view->clearCache();

echo "Готово: создано статей — {$articleCount}\n";
