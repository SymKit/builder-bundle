<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Symkit\MenuBundle\Builder\MenuNavigationBuilderInterface;
use Symkit\MenuBundle\Manager\MenuManager;
use Symkit\MenuBundle\Model\MenuLink;
use Symkit\MenuBundle\Service\ActiveMenuRegistry;
use Symkit\MenuBundle\Service\MenuModelFactory;
use Symkit\MenuBundle\Service\MenuProvider;

final class MenuManagerTest extends TestCase
{
    /**
     * @param array<string, callable(): object> $factories
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator<object>
     */
    private static function createBuildersLocator(array $factories = []): \Symfony\Component\DependencyInjection\ServiceLocator
    {
        $locator = new \Symfony\Component\DependencyInjection\ServiceLocator($factories);

        /** @var \Symfony\Component\DependencyInjection\ServiceLocator<object> $locator */
        return $locator;
    }

    public function testGetItemsDelegatesToProviderAndCaches(): void
    {
        $items = [new MenuLink('home', 'Home', '/', null)];
        $builder = $this->createMock(MenuNavigationBuilderInterface::class);
        $builder->method('build')->willReturn($items);
        $locator = self::createBuildersLocator(['primary' => static fn () => $builder]);
        $provider = new MenuProvider(null, $locator, new MenuModelFactory([]));
        $registry = new ActiveMenuRegistry();
        $manager = new MenuManager($provider, $registry);

        $result1 = $manager->getItems('primary');
        $result2 = $manager->getItems('primary');
        self::assertSame($result1, $result2);
        self::assertCount(1, $result1);
    }

    public function testGetItemsAppliesActiveIdFromRegistry(): void
    {
        $items = [new MenuLink('home', 'Home', '/', null)];
        $builder = $this->createMock(MenuNavigationBuilderInterface::class);
        $builder->method('build')->willReturn($items);
        $locator = self::createBuildersLocator(['primary' => static fn () => $builder]);
        $provider = new MenuProvider(null, $locator, new MenuModelFactory([]));
        $registry = new ActiveMenuRegistry();
        $registry->setActiveId('primary', 'home');

        $manager = new MenuManager($provider, $registry);
        $result = $manager->getItems('primary');

        self::assertCount(1, $result);
        self::assertTrue($result[0]->isActive());
    }

    public function testSetActiveIdAndHandleMetadataDelegateToRegistry(): void
    {
        $locator = self::createBuildersLocator([]);
        $provider = new MenuProvider(null, $locator, new MenuModelFactory([]));
        $registry = new ActiveMenuRegistry();
        $manager = new MenuManager($provider, $registry);

        $manager->setActiveId('primary', 'dashboard');
        self::assertSame('dashboard', $registry->getActiveId('primary'));

        $manager->handleMetadata([['menuName' => 'sidebar', 'itemId' => 'profile']]);
        self::assertSame('profile', $registry->getActiveId('sidebar'));
    }
}
