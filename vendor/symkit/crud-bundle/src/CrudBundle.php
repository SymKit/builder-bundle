<?php

declare(strict_types=1);

namespace Symkit\CrudBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symkit\CrudBundle\Component\CrudFilters;
use Symkit\CrudBundle\Component\CrudList;
use Symkit\CrudBundle\Contract\CrudListProviderInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceHandlerInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface;
use Symkit\CrudBundle\Persistence\Handler\DoctrinePersistenceHandler;
use Symkit\CrudBundle\Persistence\Manager\CrudPersistenceManager;
use Symkit\CrudBundle\Persistence\Provider\CrudListProvider;
use Symkit\CrudBundle\Twig\Component\BackLink;
use Symkit\CrudBundle\Twig\Component\DeleteForm;

class CrudBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('crud')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->integerNode('default_page_size')->defaultValue(25)->min(1)->end()
                    ->end()
                ->end()
                ->arrayNode('components')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('back_link')->defaultTrue()->end()
                        ->booleanNode('delete_form')->defaultTrue()->end()
                        ->booleanNode('crud_list')->defaultTrue()->end()
                        ->booleanNode('crud_filters')->defaultTrue()->end()
                    ->end()
                ->end()
                ->booleanNode('twig_prepend')->defaultTrue()->end()
                ->booleanNode('asset_mapper')->defaultTrue()->end()
            ->end();
    }

    /**
     * @param array{crud: array{enabled: bool}, list: array{enabled: bool, default_page_size: int}, components: array{back_link: bool, delete_form: bool, crud_list: bool, crud_filters: bool}, twig_prepend: bool, asset_mapper: bool} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();
        $crud = $config['crud']['enabled'];
        $list = $config['list']['enabled'];
        $components = $config['components'];

        if ($crud) {
            $services->set(CrudListProvider::class)->autowire();
            $services->alias(CrudListProviderInterface::class, CrudListProvider::class);
            $services->set(DoctrinePersistenceHandler::class)->autowire();
            $services->alias(CrudPersistenceHandlerInterface::class, DoctrinePersistenceHandler::class);
            $services->set(CrudPersistenceManager::class)->autowire();
            $services->alias(CrudPersistenceManagerInterface::class, CrudPersistenceManager::class);
        }

        if ($components['back_link']) {
            $services->set(BackLink::class)->autoconfigure()->tag('twig.component', ['key' => 'BackLink']);
        }
        if ($components['delete_form']) {
            $services->set(DeleteForm::class)->autoconfigure()->tag('twig.component', ['key' => 'DeleteForm']);
        }

        if ($list) {
            $services->set(CrudFilters::class)->autowire()->autoconfigure()
                ->tag('twig.component', ['key' => 'SymkitCrud:CrudFilters'])
                ->tag('ux.live_component');
            $services->set(CrudList::class)->autowire()->autoconfigure()
                ->arg('$defaultPageSize', $config['list']['default_page_size'])
                ->tag('twig.component', ['key' => 'SymkitCrud:CrudList'])
                ->tag('ux.live_component');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $path = $this->getPath();
        $configs = $builder->getExtensionConfig('symkit_crud');
        $merged = array_merge(...array_values($configs));
        $twigPrepend = $merged['twig_prepend'] ?? true;
        $assetMapper = $merged['asset_mapper'] ?? true;

        if ($twigPrepend) {
            $container->extension('twig', [
                'paths' => [
                    $path.'/templates' => 'SymkitCrud',
                ],
            ], true);
            $container->extension('twig_component', [
                'defaults' => [
                    'Symkit\CrudBundle\Twig\Component\\' => '@SymkitCrud/components/',
                ],
            ], true);
        }

        if ($assetMapper) {
            $container->extension('framework', [
                'asset_mapper' => [
                    'paths' => [
                        $path.'/assets/controllers' => 'crud',
                    ],
                ],
            ], true);
        }
    }
}
