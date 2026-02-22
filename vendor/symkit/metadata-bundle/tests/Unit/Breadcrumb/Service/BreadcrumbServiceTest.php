<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Breadcrumb\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symkit\MetadataBundle\Breadcrumb\Service\BreadcrumbService;
use Symkit\MetadataBundle\Contract\BreadcrumbBuilderInterface;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;

final class BreadcrumbServiceTest extends TestCase
{
    public function testAddAndGetItems(): void
    {
        $service = $this->createService();

        $service->add('Home', 'https://example.com/');
        $service->add('Blog', 'https://example.com/blog');

        $items = $service->getItems();

        self::assertCount(2, $items);
        self::assertSame('Home', $items[0]->name);
        self::assertSame('https://example.com/', $items[0]->url);
        self::assertSame(1, $items[0]->position);
        self::assertSame('Blog', $items[1]->name);
        self::assertSame(2, $items[1]->position);
    }

    public function testInitializeWithBuilder(): void
    {
        $builder = $this->createMock(BreadcrumbBuilderInterface::class);
        $builder->expects(self::once())
            ->method('build')
            ->willReturnCallback(static function (BreadcrumbServiceInterface $service): void {
                $service->add('Home', '/');
            });

        $service = $this->createService(['website' => $builder]);
        $service->initialize('website');

        self::assertCount(1, $service->getItems());
        self::assertSame('Home', $service->getItems()[0]->name);
    }

    public function testInitializeOnlyOnce(): void
    {
        $builder = $this->createMock(BreadcrumbBuilderInterface::class);
        $builder->expects(self::once())->method('build');

        $service = $this->createService(['website' => $builder]);
        $service->initialize('website');
        $service->initialize('website');
    }

    public function testInitializeWithUnknownContext(): void
    {
        $service = $this->createService();
        $service->initialize('unknown');

        self::assertSame([], $service->getItems());
    }

    public function testGetItemsAutoInitializes(): void
    {
        $builder = $this->createMock(BreadcrumbBuilderInterface::class);
        $builder->expects(self::once())
            ->method('build')
            ->willReturnCallback(static function (BreadcrumbServiceInterface $service): void {
                $service->add('Auto', '/auto');
            });

        $service = $this->createService(['website' => $builder]);
        $items = $service->getItems();

        self::assertCount(1, $items);
    }

    public function testIsRootRoute(): void
    {
        $builder = $this->createMock(BreadcrumbBuilderInterface::class);
        $builder->method('isRootRoute')
            ->willReturnCallback(static fn (string $route): bool => 'app_home' === $route);

        $service = $this->createService(['website' => $builder]);

        self::assertTrue($service->isRootRoute('app_home'));
        self::assertFalse($service->isRootRoute('app_blog'));
    }

    public function testIsRootRouteUnknownContext(): void
    {
        $service = $this->createService();

        self::assertFalse($service->isRootRoute('any_route', 'unknown'));
    }

    public function testReset(): void
    {
        $builder = $this->createMock(BreadcrumbBuilderInterface::class);
        $builder->expects(self::exactly(2))->method('build');

        $service = $this->createService(['website' => $builder]);
        $service->initialize('website');
        $service->add('Test', '/test');

        self::assertCount(1, $service->getItems());

        $service->reset();

        self::assertSame([], $service->getItems());
    }

    public function testAddReturnsSelf(): void
    {
        $service = $this->createService();
        $result = $service->add('Test', '/test');

        self::assertSame($service, $result);
    }

    /**
     * @param array<string, BreadcrumbBuilderInterface> $builders
     */
    private function createService(array $builders = []): BreadcrumbService
    {
        $factories = [];
        foreach ($builders as $key => $builder) {
            $factories[$key] = static fn () => $builder;
        }

        return new BreadcrumbService(new ServiceLocator($factories));
    }
}
