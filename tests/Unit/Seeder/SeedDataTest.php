<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seeder;

use App\Core\Slug;
use PHPUnit\Framework\TestCase;

/**
 * Slugs are unique in the database, so the seed data must never produce the same slug twice.
 */
final class SeedDataTest extends TestCase
{
    /**
     * @var array{
     *     categories: list<array{name: string, description: string, topics: list<string>}>,
     *     titles: list<string>,
     * }
     */
    private array $data;

    protected function setUp(): void
    {
        $this->data = require dirname(__DIR__, 3) . '/database/seed-data.php';
    }

    public function testCategorySlugsAreUnique(): void
    {
        $slugs = array_map(static fn (array $category): string => Slug::fromText($category['name']), $this->data['categories']);

        self::assertSame([], $this->duplicates($slugs));
    }

    public function testEveryPossibleArticleTitleGetsUniqueSlug(): void
    {
        $slugs = [];
        foreach ($this->data['categories'] as $category) {
            foreach ($category['topics'] as $topic) {
                foreach ($this->data['titles'] as $template) {
                    $slugs[] = Slug::fromText(sprintf($template, $topic));
                }
            }
        }

        self::assertSame([], $this->duplicates($slugs));
    }

    /**
     * @param list<string> $slugs
     * @return list<string>
     */
    private function duplicates(array $slugs): array
    {
        return array_values(array_diff_assoc($slugs, array_unique($slugs)));
    }
}
