<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArticleRepository;

/**
 * Counts article views so that reloads and scripts do not inflate the counter.
 */
final class ViewCounter
{
    public function __construct(private readonly ArticleRepository $articles)
    {
    }

    /**
     * A view is counted once a day per visitor, that is per IP address and browser.
     * Robots are not counted at all.
     */
    public function count(int $articleId): void
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($this->isRobot($userAgent)) {
            return;
        }

        // The address itself is not stored, only a hash of it together with the browser
        $visitor = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $userAgent);

        if ($this->articles->registerView($articleId, $visitor)) {
            $this->articles->incrementViews($articleId);
        }
    }

    private function isRobot(string $userAgent): bool
    {
        // Crawlers walk through every page again and again, these are not reader views
        return $userAgent === '' || preg_match('/bot|crawl|spider|slurp/i', $userAgent) === 1;
    }
}
