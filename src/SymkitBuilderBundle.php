<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symkit\BuilderBundle\Command\SyncBlocksCommand;
use Symkit\BuilderBundle\Contract\BlockRendererInterface;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Contract\BlockStrategyInterface;
use Symkit\BuilderBundle\Contract\BlockSynchronizerInterface;
use Symkit\BuilderBundle\Controller\Admin\BlockController;
use Symkit\BuilderBundle\Controller\Admin\Category\CategoryController;
use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Form\BlockCategoryType;
use Symkit\BuilderBundle\Form\BlockType;
use Symkit\BuilderBundle\LiveComponent\ContentBuilder;
use Symkit\BuilderBundle\Render\BlockRenderer;
use Symkit\BuilderBundle\Repository\BlockCategoryRepository;
use Symkit\BuilderBundle\Repository\BlockRepository;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\BuilderBundle\Service\BlockSynchronizer;
use Symkit\BuilderBundle\Service\MarkdownToBlocksService;
use Symkit\BuilderBundle\Twig\BlockExtension;
use Symkit\BuilderBundle\Validator\Constraints\BlockContentSourceValidator;

class SymkitBuilderBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register admin controllers and routes')->end()
                        ->scalarNode('route_prefix')->defaultValue('admin')->info('URL prefix for admin routes')->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register Doctrine repositories and entity-related services')->end()
                        ->arrayNode('entity')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('block_class')->defaultValue(Block::class)->info('FQCN of Block entity')->end()
                                ->scalarNode('block_repository_class')->defaultValue(BlockRepository::class)->info('FQCN of Block repository')->end()
                                ->scalarNode('block_category_class')->defaultValue(BlockCategory::class)->info('FQCN of BlockCategory entity')->end()
                                ->scalarNode('block_category_repository_class')->defaultValue(BlockCategoryRepository::class)->info('FQCN of BlockCategory repository')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register Twig extension and paths')->end()
                    ->end()
                ->end()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Prepend AssetMapper with bundle controllers')->end()
                    ->end()
                ->end()
                ->arrayNode('command')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register builder:sync-blocks command')->end()
                    ->end()
                ->end()
                ->arrayNode('live_component')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register ContentBuilder Live Component')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param array{
     *     admin?: array{enabled?: bool, route_prefix?: string},
     *     doctrine?: array{enabled?: bool, entity?: array{block_class?: string, block_repository_class?: string, block_category_class?: string, block_category_repository_class?: string}},
     *     twig?: array{enabled?: bool},
     *     assets?: array{enabled?: bool},
     *     command?: array{enabled?: bool},
     *     live_component?: array{enabled?: bool}
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $defaults = [
            'admin' => ['enabled' => true, 'route_prefix' => 'admin'],
            'doctrine' => [
                'enabled' => true,
                'entity' => [
                    'block_class' => Block::class,
                    'block_repository_class' => BlockRepository::class,
                    'block_category_class' => BlockCategory::class,
                    'block_category_repository_class' => BlockCategoryRepository::class,
                ],
            ],
            'twig' => ['enabled' => true],
            'assets' => ['enabled' => true],
            'command' => ['enabled' => true],
            'live_component' => ['enabled' => true],
        ];
        $config = array_replace_recursive($defaults, $config);

        $container->parameters()
            ->set('symkit_builder.entity.block_class', $config['doctrine']['entity']['block_class'])
            ->set('symkit_builder.entity.block_repository_class', $config['doctrine']['entity']['block_repository_class'])
            ->set('symkit_builder.entity.block_category_class', $config['doctrine']['entity']['block_category_class'])
            ->set('symkit_builder.entity.block_category_repository_class', $config['doctrine']['entity']['block_category_repository_class'])
            ->set('symkit_builder.admin.route_prefix', $config['admin']['route_prefix'])
        ;

        $services = $container->services();

        $services->instanceof(BlockStrategyInterface::class)
            ->tag('symkit.block_strategy');

        $strategyClasses = [
            Render\Strategy\SnippetBlockStrategy::class,
            Render\Strategy\ParagraphBlockStrategy::class,
            Render\Strategy\ImageBlockStrategy::class,
            Render\Strategy\QuoteBlockStrategy::class,
            Render\Strategy\TableBlockStrategy::class,
            Render\Strategy\ListBlockStrategy::class,
            Render\Strategy\CodeBlockStrategy::class,
            Render\Strategy\InfoboxBlockStrategy::class,
            Render\Strategy\CtaBlockStrategy::class,
            Render\Strategy\HowtoBlockStrategy::class,
            Render\Strategy\SeparatorBlockStrategy::class,
            Render\Strategy\VideoBlockStrategy::class,
            Render\Strategy\FaqBlockStrategy::class,
        ];

        foreach ($strategyClasses as $strategyClass) {
            $services->set($strategyClass)->autowire()->autoconfigure();
        }

        $services->set(MarkdownToBlocksService::class)->autowire()->autoconfigure();

        if ($config['doctrine']['enabled']) {
            $services->set(BlockRegistry::class)->autowire();
            $services->set($config['doctrine']['entity']['block_repository_class'])
                ->arg('$entityClass', '%symkit_builder.entity.block_class%')
                ->autowire()->autoconfigure();
            $services->set($config['doctrine']['entity']['block_category_repository_class'])
                ->arg('$entityClass', '%symkit_builder.entity.block_category_class%')
                ->autowire()->autoconfigure();
            $services->alias(BlockRepository::class, $config['doctrine']['entity']['block_repository_class']);
            $services->alias(BlockRepositoryInterface::class, $config['doctrine']['entity']['block_repository_class']);
            $services->alias(BlockCategoryRepository::class, $config['doctrine']['entity']['block_category_repository_class']);

            $services->set(BlockSynchronizer::class)
                ->arg('$projectDir', '%kernel.project_dir%')
                ->arg('$blockClass', '%symkit_builder.entity.block_class%')
                ->arg('$blockCategoryClass', '%symkit_builder.entity.block_category_class%')
                ->autowire()->autoconfigure();
            $services->alias(BlockSynchronizerInterface::class, BlockSynchronizer::class);

            $services->set(BlockType::class)
                ->arg('$blockClass', '%symkit_builder.entity.block_class%')
                ->arg('$blockCategoryClass', '%symkit_builder.entity.block_category_class%')
                ->tag('form.type');

            $services->set(BlockCategoryType::class)
                ->arg('$blockCategoryClass', '%symkit_builder.entity.block_category_class%')
                ->tag('form.type');

            $services->set(BlockContentSourceValidator::class)
                ->arg('$blockClass', '%symkit_builder.entity.block_class%')
                ->autowire()
                ->tag('validator.constraint_validator');
        }

        $services->set(BlockRenderer::class)->autowire()->autoconfigure();
        $services->alias(BlockRendererInterface::class, BlockRenderer::class);

        if ($config['admin']['enabled']) {
            $services->set(BlockController::class)
                ->arg('$blockClass', '%symkit_builder.entity.block_class%')
                ->autowire()->autoconfigure()
                ->tag('controller.service_arguments');
            $services->set(CategoryController::class)
                ->arg('$blockCategoryClass', '%symkit_builder.entity.block_category_class%')
                ->autowire()->autoconfigure()
                ->tag('controller.service_arguments');
        }

        if ($config['command']['enabled']) {
            $services->set(SyncBlocksCommand::class)
                ->autowire()
                ->tag('console.command', ['name' => 'builder:sync-blocks', 'description' => 'Synchronize blocks and categories (upsert logic).']);
        }

        if ($config['live_component']['enabled']) {
            $services->set(ContentBuilder::class)
                ->autowire()
                ->tag('twig.component', ['key' => 'content_builder'])
                ->tag('ux.live_component');
        }

        if ($config['twig']['enabled']) {
            $services->set(BlockExtension::class)
                ->autowire()->autoconfigure()
                ->tag('twig.extension');
            $path = $this->getPath();
            $container->extension('twig', ['paths' => [$path.'/templates' => 'SymkitBuilder']], true);
            $container->extension('twig_component', [
                'defaults' => [
                    'Symkit\BuilderBundle\LiveComponent\\' => '@SymkitBuilder/live_component/',
                ],
            ], true);
            $container->extension('framework', [
                'translator' => [
                    'paths' => [$path.'/translations'],
                ],
            ], true);
        }

        if ($config['assets']['enabled']) {
            $path = $this->getPath();
            $container->extension('framework', [
                'asset_mapper' => [
                    'paths' => [$path.'/assets/controllers' => 'builder'],
                ],
            ], true);
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Prepend is done conditionally in loadExtension (twig, assets)
    }
}
