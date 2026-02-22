<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symkit\FormBundle\Service\HeroiconProvider;

final class HeroiconProviderTest extends TestCase
{
    public function testGetAllIconsReturnsNonEmptyArray(): void
    {
        $provider = new HeroiconProvider();
        $icons = $provider->getAllIcons();

        self::assertNotEmpty($icons);
    }

    public function testGetAllIconsReturnsKeyValuePairs(): void
    {
        $provider = new HeroiconProvider();
        $icons = $provider->getAllIcons();

        foreach ($icons as $key => $label) {
            self::assertIsString($key);
            self::assertIsString($label);
            self::assertStringStartsWith('heroicons:', $key);
        }
    }

    public function testGetAllIconsWithNullStyleReturnsAllStyles(): void
    {
        $provider = new HeroiconProvider();
        $defaultIcons = $provider->getAllIcons();
        $allIcons = $provider->getAllIcons(null);

        self::assertGreaterThan(\count($defaultIcons), \count($allIcons));
    }
}
