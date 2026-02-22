<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\DependencyInjection;

use Symkit\BuilderBundle\Command\SyncBlocksCommand;
use Symkit\BuilderBundle\Controller\Admin\BlockController;
use Symkit\BuilderBundle\Controller\Admin\Category\CategoryController;
use Symkit\BuilderBundle\Form\BlockCategoryType;
use Symkit\BuilderBundle\Form\BlockType;
use Symkit\BuilderBundle\LiveComponent\ContentBuilder;
use Symkit\BuilderBundle\Repository\BlockCategoryRepository;
use Symkit\BuilderBundle\Repository\BlockRepository;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\BuilderBundle\Service\BlockSynchronizer;
use Symkit\BuilderBundle\Service\MarkdownToBlocksService;
use Symkit\BuilderBundle\Validator\Constraints\BlockContentSourceValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

final class BuilderExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register(BlockRepository::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $container->register(BlockCategoryRepository::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $container->register(BlockRegistry::class)
            ->setAutowired(true)
        ;

        $container->register(MarkdownToBlocksService::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $container->register(BlockSynchronizer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$projectDir', '%kernel.project_dir%')
        ;

        $container->register(SyncBlocksCommand::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('console.command')
        ;

        $container->register(BlockType::class)
            ->addTag('form.type')
        ;

        $container->register(BlockCategoryType::class)
            ->addTag('form.type')
        ;

        $container->register(BlockContentSourceValidator::class)
            ->setAutowired(true)
            ->addTag('validator.constraint_validator')
        ;

        $container->register(BlockController::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('controller.service_arguments')
        ;

        $container->register(CategoryController::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('controller.service_arguments')
        ;

        $container->register(ContentBuilder::class)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('twig.component', ['key' => 'content_builder'])
            ->addTag('ux.live_component')
        ;

        $container->register(\Symkit\BuilderBundle\Render\BlockRenderer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $container->setAlias(\Symkit\BuilderBundle\Render\BlockRendererInterface::class, \Symkit\BuilderBundle\Render\BlockRenderer::class);

        $container->registerForAutoconfiguration(\Symkit\BuilderBundle\Render\BlockStrategyInterface::class)
            ->addTag('symkit.block_strategy')
        ;

        $strategies = [
            \Symkit\BuilderBundle\Render\Strategy\SnippetBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\ParagraphBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\ImageBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\QuoteBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\TableBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\ListBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\CodeBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\InfoboxBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\CtaBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\HowtoBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\SeparatorBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\VideoBlockStrategy::class,
            \Symkit\BuilderBundle\Render\Strategy\FaqBlockStrategy::class,
        ];

        foreach ($strategies as $strategyClass) {
            $container->register($strategyClass)
                ->setAutowired(true)
                ->setAutoconfigured(true)
            ;
        }

        $container->register(\Symkit\BuilderBundle\Twig\BlockExtension::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('twig.extension')
        ;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundleDir = \dirname(__DIR__, 2);

        $container->prependExtensionConfig('twig', [
            'paths' => [
                $bundleDir . '/templates' => 'SymkitBuilder',
            ],
        ]);

        $container->prependExtensionConfig('twig_component', [
            'defaults' => [
                'Symkit\BuilderBundle\LiveComponent\\' => '@SymkitBuilder/live_component/',
            ],
        ]);

        $container->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $bundleDir . '/assets/controllers' => 'builder',
                ],
            ],
            'translator' => [
                'paths' => [$bundleDir . '/translations'],
            ],
        ]);
    }
}
