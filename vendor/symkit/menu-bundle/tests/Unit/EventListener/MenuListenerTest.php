<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symkit\MenuBundle\EventListener\MenuListener;
use Symkit\MenuBundle\Manager\MenuManager;
use Symkit\MenuBundle\Service\ActiveMenuRegistry;
use Symkit\MenuBundle\Service\MenuModelFactory;
use Symkit\MenuBundle\Service\MenuProvider;

final class MenuListenerTest extends TestCase
{
    /** @return ServiceLocator<object> */
    private static function createBuildersLocator(): ServiceLocator
    {
        $locator = new ServiceLocator([]);

        /** @var ServiceLocator<object> $locator */
        return $locator;
    }

    public function testOnKernelControllerCallsHandleMetadataWhenControllerInMetadata(): void
    {
        $request = new Request();
        $request->attributes->set('_controller', 'App\Controller\FooController::index');

        $registry = new ActiveMenuRegistry();
        $provider = new MenuProvider(null, self::createBuildersLocator(), new MenuModelFactory([]));
        $menuManager = new MenuManager($provider, $registry);

        $listener = new MenuListener($menuManager, [
            'App\Controller\FooController::index' => [
                ['menuName' => 'primary', 'itemId' => 'home'],
            ],
        ]);

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new ControllerEvent($kernel, static fn () => null, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelController($event);

        self::assertSame('home', $registry->getActiveId('primary'));
    }

    public function testOnKernelControllerDoesNothingWhenControllerNotInMetadata(): void
    {
        $request = new Request();
        $request->attributes->set('_controller', 'OtherController::action');

        $registry = new ActiveMenuRegistry();
        $provider = new MenuProvider(null, self::createBuildersLocator(), new MenuModelFactory([]));
        $menuManager = new MenuManager($provider, $registry);

        $listener = new MenuListener($menuManager, [
            'App\Controller\FooController::index' => [['menuName' => 'primary', 'itemId' => 'home']],
        ]);

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new ControllerEvent($kernel, static fn () => null, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelController($event);

        self::assertNull($registry->getActiveId('primary'));
    }
}
