<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Listener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Breadcrumb\Service\BreadcrumbService;
use Symkit\MetadataBundle\Listener\BreadcrumbListener;

final class BreadcrumbListenerTest extends TestCase
{
    public function testBreadcrumbWithUrlItems(): void
    {
        $breadcrumbService = new BreadcrumbService(new ServiceLocator([]));
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $listener = new BreadcrumbListener($breadcrumbService, $urlGenerator);

        $controller = new class {
            #[Breadcrumb(items: [
                ['label' => 'Home', 'url' => 'https://example.com/'],
                ['label' => 'Blog', 'url' => 'https://example.com/blog'],
            ])]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        $items = $breadcrumbService->getItems();
        self::assertCount(2, $items);
        self::assertSame('Home', $items[0]->name);
        self::assertSame('https://example.com/', $items[0]->url);
        self::assertSame('Blog', $items[1]->name);
    }

    public function testBreadcrumbWithRouteItems(): void
    {
        $breadcrumbService = new BreadcrumbService(new ServiceLocator([]));
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('blog_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/blog');

        $listener = new BreadcrumbListener($breadcrumbService, $urlGenerator);

        $controller = new class {
            #[Breadcrumb(items: [
                ['label' => 'Blog', 'route' => 'blog_index'],
            ])]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        $items = $breadcrumbService->getItems();
        self::assertCount(1, $items);
        self::assertSame('https://example.com/blog', $items[0]->url);
    }

    public function testNoBreadcrumbAttribute(): void
    {
        $breadcrumbService = new BreadcrumbService(new ServiceLocator([]));
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $listener = new BreadcrumbListener($breadcrumbService, $urlGenerator);

        $controller = new class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        self::assertSame([], $breadcrumbService->getItems());
    }

    public function testSubRequestIsIgnored(): void
    {
        $breadcrumbService = new BreadcrumbService(new ServiceLocator([]));
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $listener = new BreadcrumbListener($breadcrumbService, $urlGenerator);

        $controller = new class {
            #[Breadcrumb(items: [['label' => 'Home', 'url' => '/']])]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action'], false);
        $listener->onKernelController($event);

        self::assertSame([], $breadcrumbService->getItems());
    }

    public function testCustomContext(): void
    {
        $breadcrumbService = new BreadcrumbService(new ServiceLocator([]));
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $listener = new BreadcrumbListener($breadcrumbService, $urlGenerator);

        $controller = new class {
            #[Breadcrumb(context: 'admin', items: [['label' => 'Dashboard', 'url' => '/admin']])]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        $items = $breadcrumbService->getItems();
        self::assertCount(1, $items);
        self::assertSame('Dashboard', $items[0]->name);
    }

    /**
     * @param callable|array{object, string} $controller
     */
    private function createEvent(callable|array $controller, bool $mainRequest = true): ControllerEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new ControllerEvent(
            $kernel,
            $controller,
            $request,
            $mainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
        );
    }
}
