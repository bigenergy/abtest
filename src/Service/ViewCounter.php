<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArticleRepository;

/**
 * Counts article views so that reloading a page does not inflate the counter.
 */
final class ViewCounter
{
    private const SESSION_KEY = 'viewed_articles';

    public function __construct(private readonly ArticleRepository $articles)
    {
    }

    /**
     * A view is counted once per visitor session; robots are not counted at all.
     */
    public function count(int $articleId): void
    {
        if ($this->isRobot($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
        }

        if (isset($_SESSION[self::SESSION_KEY][$articleId])) {
            return;
        }

        $_SESSION[self::SESSION_KEY][$articleId] = true;
        $this->articles->incrementViews($articleId);
    }

    private function isRobot(string $userAgent): bool
    {
        // Robots do not keep cookies, so every request of theirs would look like a new visitor
        return $userAgent === '' || preg_match('/bot|crawl|spider|slurp/i', $userAgent) === 1;
    }
}
