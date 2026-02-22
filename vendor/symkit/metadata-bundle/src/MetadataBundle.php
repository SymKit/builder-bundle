<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\KernelEvents;
use Symkit\MetadataBundle\Breadcrumb\Service\BreadcrumbService;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\Contract\BreadcrumbBuilderInterface;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symkit\MetadataBundle\Contract\JsonLdCollectorInterface;
use Symkit\MetadataBundle\Contract\JsonLdPopulatorInterface;
use Symkit\MetadataBundle\Contract\MetadataPopulatorInterface;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;
use Symkit\MetadataBundle\Contract\PageContextProviderInterface;
use Symkit\MetadataBundle\Contract\SchemaGeneratorInterface;
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;
use Symkit\MetadataBundle\JsonLd\Collector\JsonLdCollector;
use Symkit\MetadataBundle\JsonLd\Generator\BreadcrumbSchemaGenerator;
use Symkit\MetadataBundle\JsonLd\Generator\WebPageSchemaGenerator;
use Symkit\MetadataBundle\JsonLd\Generator\WebSiteSchemaGenerator;
use Symkit\MetadataBundle\JsonLd\Renderer\JsonLdRenderer;
use Symkit\MetadataBundle\JsonLd\Service\JsonLdService;
use Symkit\MetadataBundle\Listener\BreadcrumbListener;
use Symkit\MetadataBundle\Listener\SeoListener;
use Symkit\MetadataBundle\Renderer\MetaTagRenderer;
use Symkit\MetadataBundle\Twig\BreadcrumbTwigExtension;
use Symkit\MetadataBundle\Twig\JsonLdTwigExtension;
use Symkit\MetadataBundle\Twig\SeoTwigExtension;

class MetadataBundle extends AbstractBundle
{
    protected string $extensionAlias = 'symkit_metadata';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('site_info_provider')
                    ->defaultValue(SiteInfoProviderInterface::class)
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
    }

    /**
     * @param array{
     *     site_info_provider: string,
     *     meta_tags: array{enabled: bool, title_format: string, twitter_site: ?string, twitter_creator: ?string},
     *     json_ld: array{enabled: bool},
     *     breadcrumbs: array{enabled: bool},
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services()
            ->defaults()
                ->autowire()
                ->autoconfigure();

        $builder->setAlias(SiteInfoProviderInterface::class, $config['site_info_provider']);

        $services->set(PageContextBuilder::class);
        $services->alias(PageContextBuilderInterface::class, PageContextBuilder::class);
        $services->alias(PageContextProviderInterface::class, PageContextBuilder::class);

        $builder->registerForAutoconfiguration(MetadataPopulatorInterface::class)
            ->addTag('symkit_metadata.populator');

        if ($config['meta_tags']['enabled']) {
            $services->set(MetaTagRenderer::class)
                ->arg('$titleFormat', $config['meta_tags']['title_format'])
                ->arg('$twitterSite', $config['meta_tags']['twitter_site'])
                ->arg('$twitterCreator', $config['meta_tags']['twitter_creator']);

            $services->set(SeoListener::class)
                ->tag('kernel.event_listener', ['event' => KernelEvents::CONTROLLER, 'method' => 'onKernelController']);

            $services->set(SeoTwigExtension::class)
                ->tag('twig.extension');
        }

        if ($config['json_ld']['enabled']) {
            $services->set(JsonLdCollector::class);
            $services->alias(JsonLdCollectorInterface::class, JsonLdCollector::class);

            $services->set(JsonLdService::class)
                ->arg('$generators', tagged_iterator('symkit_metadata.schema_generator'));

            $services->set(JsonLdRenderer::class);

            $services->set(WebSiteSchemaGenerator::class)
                ->tag('symkit_metadata.schema_generator');
            $services->set(WebPageSchemaGenerator::class)
                ->tag('symkit_metadata.schema_generator');

            if ($config['breadcrumbs']['enabled']) {
                $services->set(BreadcrumbSchemaGenerator::class)
                    ->tag('symkit_metadata.schema_generator');
            }

            $services->set(JsonLdTwigExtension::class)
                ->tag('twig.extension');

            $builder->registerForAutoconfiguration(JsonLdPopulatorInterface::class)
                ->addTag('symkit_metadata.jsonld_populator');

            $builder->registerForAutoconfiguration(SchemaGeneratorInterface::class)
                ->addTag('symkit_metadata.schema_generator');
        }

        if ($config['breadcrumbs']['enabled']) {
            $services->set(BreadcrumbService::class)
                ->arg('$builders', tagged_locator('symkit_metadata.breadcrumb_builder', 'index'));
            $services->alias(BreadcrumbServiceInterface::class, BreadcrumbService::class);

            $services->set(BreadcrumbListener::class)
                ->tag('kernel.event_listener', ['event' => KernelEvents::CONTROLLER, 'method' => 'onKernelController']);

            $services->set(BreadcrumbTwigExtension::class)
                ->tag('twig.extension');

            $builder->registerForAutoconfiguration(BreadcrumbBuilderInterface::class)
                ->addTag('symkit_metadata.breadcrumb_builder');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('twig', [
            'paths' => [
                $this->getPath().'/templates' => 'SymkitMetadata',
            ],
        ]);
    }
}
