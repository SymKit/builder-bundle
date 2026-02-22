<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symkit\MetadataBundle\MetadataBundle;

final class MetadataBundleTest extends TestCase
{
    public function testExtendsAbstractBundle(): void
    {
        $bundle = new MetadataBundle();

        self::assertInstanceOf(AbstractBundle::class, $bundle);
    }

    public function testExtensionAlias(): void
    {
        $bundle = new MetadataBundle();

        self::assertSame('symkit_metadata', $bundle->getContainerExtension()->getAlias());
    }

    public function testDefaultConfiguration(): void
    {
        $config = $this->processConfig([]);

        self::assertSame('Symkit\MetadataBundle\Contract\SiteInfoProviderInterface', $config['site_info_provider']);
        self::assertTrue($config['meta_tags']['enabled']);
        self::assertSame('{title} | {siteName}', $config['meta_tags']['title_format']);
        self::assertNull($config['meta_tags']['twitter_site']);
        self::assertNull($config['meta_tags']['twitter_creator']);
        self::assertTrue($config['json_ld']['enabled']);
        self::assertTrue($config['breadcrumbs']['enabled']);
    }

    public function testDisableMetaTags(): void
    {
        $config = $this->processConfig([
            'meta_tags' => ['enabled' => false],
        ]);

        self::assertFalse($config['meta_tags']['enabled']);
        self::assertTrue($config['json_ld']['enabled']);
        self::assertTrue($config['breadcrumbs']['enabled']);
    }

    public function testDisableJsonLd(): void
    {
        $config = $this->processConfig([
            'json_ld' => ['enabled' => false],
        ]);

        self::assertTrue($config['meta_tags']['enabled']);
        self::assertFalse($config['json_ld']['enabled']);
    }

    public function testDisableBreadcrumbs(): void
    {
        $config = $this->processConfig([
            'breadcrumbs' => ['enabled' => false],
        ]);

        self::assertTrue($config['meta_tags']['enabled']);
        self::assertFalse($config['breadcrumbs']['enabled']);
    }

    public function testDisableAll(): void
    {
        $config = $this->processConfig([
            'meta_tags' => ['enabled' => false],
            'json_ld' => ['enabled' => false],
            'breadcrumbs' => ['enabled' => false],
        ]);

        self::assertFalse($config['meta_tags']['enabled']);
        self::assertFalse($config['json_ld']['enabled']);
        self::assertFalse($config['breadcrumbs']['enabled']);
    }

    public function testCustomTitleFormat(): void
    {
        $config = $this->processConfig([
            'meta_tags' => ['title_format' => '{title} - {siteName}'],
        ]);

        self::assertSame('{title} - {siteName}', $config['meta_tags']['title_format']);
    }

    public function testCustomSiteInfoProvider(): void
    {
        $config = $this->processConfig([
            'site_info_provider' => 'App\\Provider\\MySiteInfoProvider',
        ]);

        self::assertSame('App\\Provider\\MySiteInfoProvider', $config['site_info_provider']);
    }

    public function testTwitterConfiguration(): void
    {
        $config = $this->processConfig([
            'meta_tags' => [
                'twitter_site' => '@mysite',
                'twitter_creator' => '@author',
            ],
        ]);

        self::assertSame('@mysite', $config['meta_tags']['twitter_site']);
        self::assertSame('@author', $config['meta_tags']['twitter_creator']);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function processConfig(array $config): array
    {
        $treeBuilder = new TreeBuilder('symkit_metadata');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('site_info_provider')
                    ->defaultValue('Symkit\MetadataBundle\Contract\SiteInfoProviderInterface')
                ->end()
                ->arrayNode('meta_tags')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('title_format')
                            ->defaultValue('{title} | {siteName}')
                        ->end()
                        ->scalarNode('twitter_site')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('twitter_creator')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('json_ld')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                ->end()
                ->arrayNode('breadcrumbs')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                ->end()
            ->end();

        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), [$config]);
    }
}
