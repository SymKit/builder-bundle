<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symkit\MenuBundle\Builder\MenuNavigationBuilderInterface;
use Symkit\MenuBundle\Model\MenuLink;
use Symkit\MenuBundle\Service\MenuModelFactory;
use Symkit\MenuBundle\Service\MenuProvider;

final class MenuProviderTest extends TestCase
{
    /**
     * @param array<string, callable(): object> $factories
     *
     * @return ServiceLocator<object>
     */
    private static function createBuildersLocator(array $factories = []): ServiceLocator
    {
        $locator = new ServiceLocator($factories);

        /** @var ServiceLocator<object> $locator */
        return $locator;
    }

    public function testGetItemsFromBuilderWhenNoRepository(): void
    {
        $items = [new MenuLink('home', 'Home', '/', null)];
        $builder = $this->createMock(MenuNavigationBuilderInterface::class);
        $builder->method('build')->willReturn($items);

        $locator = self::createBuildersLocator([
            'primary' => function () use ($builder) {
                return $builder;
            },
        ]);
        $modelFactory = new MenuModelFactory([]);

        $provider = new MenuProvider(null, $locator, $modelFactory);

        self::assertSame($items, $provider->getItems('primary'));
    }

    public function testGetItemsReturnsEmptyWhenBuilderNotFound(): void
    {
        $locator = self::createBuildersLocator([]);
        $modelFactory = new MenuModelFactory([]);

        $provider = new MenuProvider(null, $locator, $modelFactory);

        self::assertSame([], $provider->getItems('unknown'));
    }

    public function testGetItemsReturnsEmptyWhenBuilderDoesNotImplementInterface(): void
    {
        $locator = self::createBuildersLocator([
            'primary' => function () {
                return new stdClass();
            },
        ]);
        $modelFactory = new MenuModelFactory([]);

        $provider = new MenuProvider(null, $locator, $modelFactory);

        self::assertSame([], $provider->getItems('primary'));
    }
}
