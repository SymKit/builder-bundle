<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Twig\Component;

use PHPUnit\Framework\TestCase;
use Symkit\CrudBundle\Twig\Component\BackLink;

final class BackLinkTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $component = new BackLink();

        self::assertSame([], $component->routeParams);
        self::assertSame('crud.component.back_link.label', $component->label);
        self::assertSame('heroicons:arrow-left-20-solid', $component->icon);
    }

    public function testPropertiesCanBeSet(): void
    {
        $component = new BackLink();
        $component->route = 'app_dashboard';
        $component->routeParams = ['id' => 5];
        $component->label = 'Go back';
        $component->icon = 'heroicons:chevron-left-20-solid';

        self::assertSame('app_dashboard', $component->route);
        self::assertSame(['id' => 5], $component->routeParams);
        self::assertSame('Go back', $component->label);
        self::assertSame('heroicons:chevron-left-20-solid', $component->icon);
    }
}
