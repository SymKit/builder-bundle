<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\MenuBundle\Controller\Admin\Api\ItemsController;
use Symkit\MenuBundle\Controller\Admin\MenuController;
use Symkit\MenuBundle\Controller\Admin\MenuItemController;
use Symkit\MenuBundle\SymkitMenuBundle;
use Symkit\MenuBundle\Twig\MenuExtension;

final class ConfigServicesTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(SymkitMenuBundle::class);
        /** @var array{doctrine?: array{enabled?: bool}, admin?: array{enabled?: bool}, twig?: array{enabled?: bool}, assets?: array{enabled?: bool}} $config */
        $config = $options['symkit_menu_config'] ?? [
            'doctrine' => ['enabled' => false],
            'admin' => ['enabled' => false],
            'twig' => ['enabled' => false],
            'assets' => ['enabled' => false],
        ];
        if (($config['twig']['enabled'] ?? false) === true) {
            $kernel->addTestBundle(TwigBundle::class);
        }
        $kernel->addTestConfig(static function ($container) use ($config): void {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
                'form' => ['enabled' => true],
                'csrf_protection' => false,
            ]);
            $container->loadFromExtension('symkit_menu', $config);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testAdminDisabledControllersNotRegistered(): void
    {
        self::bootKernel(['symkit_menu_config' => [
            'doctrine' => ['enabled' => false],
            'admin' => ['enabled' => false],
            'twig' => ['enabled' => false],
            'assets' => ['enabled' => false],
        ]]);
        $container = static::getContainer();

        self::assertFalse($container->has(ItemsController::class));
        self::assertFalse($container->has(MenuController::class));
        self::assertFalse($container->has(MenuItemController::class));
    }

    public function testTwigDisabledExtensionNotRegistered(): void
    {
        self::bootKernel(['symkit_menu_config' => [
            'doctrine' => ['enabled' => false],
            'admin' => ['enabled' => false],
            'twig' => ['enabled' => false],
            'assets' => ['enabled' => false],
        ]]);
        $container = static::getContainer();

        self::assertFalse($container->has(MenuExtension::class));
    }

    public function testTwigEnabledExtensionRegistered(): void
    {
        self::bootKernel(['symkit_menu_config' => [
            'doctrine' => ['enabled' => false],
            'admin' => ['enabled' => false],
            'twig' => ['enabled' => true],
            'assets' => ['enabled' => false],
        ]]);
        $container = static::getContainer();

        self::assertTrue($container->has(MenuExtension::class));
    }

    public function testParametersHaveDefaults(): void
    {
        self::bootKernel(['symkit_menu_config' => [
            'doctrine' => ['enabled' => false],
            'admin' => ['enabled' => false],
            'twig' => ['enabled' => false],
            'assets' => ['enabled' => false],
        ]]);
        $container = static::getContainer();

        self::assertSame('Symkit\MenuBundle\Entity\Menu', $container->getParameter('symkit_menu.entity.menu'));
        self::assertSame('Symkit\MenuBundle\Entity\MenuItem', $container->getParameter('symkit_menu.entity.menu_item'));
        self::assertSame('admin', $container->getParameter('symkit_menu.admin.route_prefix'));
    }
}
