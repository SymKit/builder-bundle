<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\MetadataBundle\Breadcrumb\Service\BreadcrumbService;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symkit\MetadataBundle\Contract\JsonLdCollectorInterface;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;
use Symkit\MetadataBundle\Contract\PageContextProviderInterface;
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;
use Symkit\MetadataBundle\JsonLd\Collector\JsonLdCollector;
use Symkit\MetadataBundle\JsonLd\Generator\BreadcrumbSchemaGenerator;
use Symkit\MetadataBundle\JsonLd\Generator\WebPageSchemaGenerator;
use Symkit\MetadataBundle\JsonLd\Generator\WebSiteSchemaGenerator;
use Symkit\MetadataBundle\JsonLd\Renderer\JsonLdRenderer;
use Symkit\MetadataBundle\JsonLd\Service\JsonLdService;
use Symkit\MetadataBundle\Listener\BreadcrumbListener;
use Symkit\MetadataBundle\Listener\SeoListener;
use Symkit\MetadataBundle\MetadataBundle;
use Symkit\MetadataBundle\Renderer\MetaTagRenderer;
use Symkit\MetadataBundle\Tests\Integration\Stub\StubSiteInfoProvider;
use Symkit\MetadataBundle\Twig\BreadcrumbTwigExtension;
use Symkit\MetadataBundle\Twig\JsonLdTwigExtension;
use Symkit\MetadataBundle\Twig\SeoTwigExtension;

final class MetadataBundleIntegrationTest extends KernelTestCase
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

    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(TwigBundle::class);
        $kernel->addTestBundle(MetadataBundle::class);
        $kernel->addTestConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', ['test' => true, 'http_method_override' => false]);
            $container->register(StubSiteInfoProvider::class, StubSiteInfoProvider::class)->setPublic(true);
            $container->loadFromExtension('symkit_metadata', [
                'site_info_provider' => StubSiteInfoProvider::class,
            ]);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testBundleBoots(): void
    {
        self::bootKernel();

        self::assertArrayHasKey('MetadataBundle', self::$kernel->getBundles());
    }

    public function testCoreServicesAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(PageContextBuilder::class));
        self::assertTrue($container->has(PageContextBuilderInterface::class));
        self::assertTrue($container->has(PageContextProviderInterface::class));
    }

    public function testPageContextBuilderAliasesPointToSameInstance(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $builder = $container->get(PageContextBuilderInterface::class);
        $provider = $container->get(PageContextProviderInterface::class);

        self::assertSame($builder, $provider);
    }

    public function testMetaTagServicesAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(MetaTagRenderer::class));
        self::assertTrue($container->has(SeoListener::class));
        self::assertTrue($container->has(SeoTwigExtension::class));
    }

    public function testJsonLdServicesAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(JsonLdCollector::class));
        self::assertTrue($container->has(JsonLdCollectorInterface::class));
        self::assertTrue($container->has(JsonLdService::class));
        self::assertTrue($container->has(JsonLdRenderer::class));
        self::assertTrue($container->has(WebSiteSchemaGenerator::class));
        self::assertTrue($container->has(WebPageSchemaGenerator::class));
        self::assertTrue($container->has(BreadcrumbSchemaGenerator::class));
        self::assertTrue($container->has(JsonLdTwigExtension::class));
    }

    public function testBreadcrumbServicesAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(BreadcrumbService::class));
        self::assertTrue($container->has(BreadcrumbServiceInterface::class));
        self::assertTrue($container->has(BreadcrumbListener::class));
        self::assertTrue($container->has(BreadcrumbTwigExtension::class));
    }

    public function testDisableMetaTags(): void
    {
        $kernel = self::createKernelWithConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('symkit_metadata', [
                'meta_tags' => ['enabled' => false],
            ]);
        });
        $kernel->boot();
        $container = $kernel->getContainer()->get('test.service_container');

        self::assertFalse($container->has(MetaTagRenderer::class));
        self::assertFalse($container->has(SeoListener::class));
        self::assertFalse($container->has(SeoTwigExtension::class));

        self::assertTrue($container->has(JsonLdCollector::class));
        self::assertTrue($container->has(BreadcrumbService::class));
    }

    public function testDisableJsonLd(): void
    {
        $kernel = self::createKernelWithConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('symkit_metadata', [
                'json_ld' => ['enabled' => false],
            ]);
        });
        $kernel->boot();
        $container = $kernel->getContainer()->get('test.service_container');

        self::assertFalse($container->has(JsonLdCollector::class));
        self::assertFalse($container->has(JsonLdService::class));
        self::assertFalse($container->has(JsonLdRenderer::class));
        self::assertFalse($container->has(JsonLdTwigExtension::class));

        self::assertTrue($container->has(MetaTagRenderer::class));
        self::assertTrue($container->has(BreadcrumbService::class));
    }

    public function testDisableBreadcrumbs(): void
    {
        $kernel = self::createKernelWithConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('symkit_metadata', [
                'breadcrumbs' => ['enabled' => false],
            ]);
        });
        $kernel->boot();
        $container = $kernel->getContainer()->get('test.service_container');

        self::assertFalse($container->has(BreadcrumbService::class));
        self::assertFalse($container->has(BreadcrumbListener::class));
        self::assertFalse($container->has(BreadcrumbTwigExtension::class));

        self::assertTrue($container->has(MetaTagRenderer::class));
        self::assertTrue($container->has(JsonLdCollector::class));
    }

    public function testDisableAll(): void
    {
        $kernel = self::createKernelWithConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('symkit_metadata', [
                'meta_tags' => ['enabled' => false],
                'json_ld' => ['enabled' => false],
                'breadcrumbs' => ['enabled' => false],
            ]);
        });
        $kernel->boot();
        $container = $kernel->getContainer()->get('test.service_container');

        self::assertFalse($container->has(MetaTagRenderer::class));
        self::assertFalse($container->has(JsonLdCollector::class));
        self::assertFalse($container->has(BreadcrumbService::class));
        self::assertFalse($container->has(SeoListener::class));
        self::assertFalse($container->has(BreadcrumbListener::class));
    }

    public function testTwitterConfigurationInjected(): void
    {
        $kernel = self::createKernelWithConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('symkit_metadata', [
                'meta_tags' => [
                    'twitter_site' => '@mysite',
                    'twitter_creator' => '@author',
                ],
            ]);
        });
        $kernel->boot();
        $container = $kernel->getContainer()->get('test.service_container');

        self::assertTrue($container->has(MetaTagRenderer::class));
    }

    public function testSiteInfoProviderAlias(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $provider = $container->get(SiteInfoProviderInterface::class);
        self::assertInstanceOf(StubSiteInfoProvider::class, $provider);
    }

    private static function createKernelWithConfig(callable $configCallback): TestKernel
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel();
        $kernel->addTestBundle(TwigBundle::class);
        $kernel->addTestBundle(MetadataBundle::class);
        $kernel->addTestConfig(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', ['test' => true, 'http_method_override' => false]);
            $container->register(StubSiteInfoProvider::class, StubSiteInfoProvider::class)->setPublic(true);
            $container->loadFromExtension('symkit_metadata', [
                'site_info_provider' => StubSiteInfoProvider::class,
            ]);
        });
        $kernel->addTestConfig($configCallback);
        $kernel->handleOptions([]);

        return $kernel;
    }
}
