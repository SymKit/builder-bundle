<?php

declare(strict_types=1);

namespace Symkit\MenuBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symkit\MenuBundle\DependencyInjection\Compiler\MenuListenerPass;

class SymkitMenuBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('admin')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register admin controllers and API')->end()
                        ->scalarNode('route_prefix')->defaultValue('admin')->info('Route prefix for admin routes')->end()
                    ->end()
                ->end()
                ->arrayNode('twig')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register Twig extension and paths')->end()
                    ->end()
                ->end()
                ->arrayNode('assets')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Prepend AssetMapper with bundle controllers')->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->info('Register repositories and Doctrine-related services')->end()
                        ->arrayNode('entities')
                            ->children()
                                ->scalarNode('menu')->defaultValue('Symkit\MenuBundle\Entity\Menu')->info('FQCN of Menu entity')->end()
                                ->scalarNode('menu_item')->defaultValue('Symkit\MenuBundle\Entity\MenuItem')->info('FQCN of MenuItem entity')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param array{
     *     admin?: array{enabled?: bool, route_prefix?: string},
     *     twig?: array{enabled?: bool},
     *     assets?: array{enabled?: bool},
     *     doctrine?: array{enabled?: bool, entities?: array{menu?: string, menu_item?: string}}
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $defaults = [
            'admin' => ['enabled' => true, 'route_prefix' => 'admin'],
            'twig' => ['enabled' => true],
            'assets' => ['enabled' => true],
            'doctrine' => [
                'enabled' => true,
                'entities' => [
                    'menu' => 'Symkit\MenuBundle\Entity\Menu',
                    'menu_item' => 'Symkit\MenuBundle\Entity\MenuItem',
                ],
            ],
        ];
        $config = array_replace_recursive($defaults, $config);

        $container->parameters()
            ->set('symkit_menu.entity.menu', $config['doctrine']['entities']['menu'])
            ->set('symkit_menu.entity.menu_item', $config['doctrine']['entities']['menu_item'])
            ->set('symkit_menu.admin.route_prefix', $config['admin']['route_prefix'])
        ;

        $services = $container->services();

        $services->instanceof(Builder\MenuNavigationBuilderInterface::class)
            ->tag('symkit_menu.menu_builder');

        $services->instanceof(Mapper\MenuItemMapperInterface::class)
            ->tag('symkit_menu.menu_item_mapper');

        $coreServices = [
            EventListener\MenuListener::class,
            Service\MenuItemChoicesProvider::class,
            Manager\MenuManager::class,
        ];

        foreach ($coreServices as $class) {
            $services->set($class)->autowire()->autoconfigure();
        }

        $services->set(Service\MenuItemPositionManager::class)
            ->arg('$menuItemEntityClass', '%symkit_menu.entity.menu_item%')
            ->autowire()->autoconfigure();

        if ($config['doctrine']['enabled']) {
            $services->set(Repository\MenuRepository::class)
                ->arg('$entityClass', '%symkit_menu.entity.menu%')
                ->autowire()->autoconfigure();
            $services->set(Repository\MenuItemRepository::class)
                ->arg('$entityClass', '%symkit_menu.entity.menu_item%')
                ->autowire()->autoconfigure();
            $services->set(EventListener\MenuItemPositionListener::class)
                ->autowire()->autoconfigure()
                ->tag('kernel.event_listener', ['event' => \Symkit\CrudBundle\Enum\CrudEvents::POST_PERSIST->value, 'method' => 'onCrudEvent'])
                ->tag('kernel.event_listener', ['event' => \Symkit\CrudBundle\Enum\CrudEvents::POST_UPDATE->value, 'method' => 'onCrudEvent']);
        }

        $services->set(Form\MenuType::class)
            ->arg('$menuEntityClass', '%symkit_menu.entity.menu%')
            ->autowire()->autoconfigure();
        $services->set(Form\MenuItemType::class)
            ->arg('$menuItemEntityClass', '%symkit_menu.entity.menu_item%')
            ->autowire()->autoconfigure();

        $services->set(Service\MenuModelFactory::class)
            ->arg('$mappers', \Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator('symkit_menu.menu_item_mapper'))
            ->autowire()->autoconfigure();

        $services->set(Service\ActiveMenuRegistry::class)->autowire()->autoconfigure();

        $menuProviderDef = $services->set(Service\MenuProvider::class)
            ->arg('$builders', \Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator('symkit_menu.menu_builder'))
            ->autowire()->autoconfigure();
        if (!$config['doctrine']['enabled']) {
            $menuProviderDef->arg('$menuRepository', null);
        }

        foreach ([Mapper\LinkMapper::class, Mapper\DropdownMapper::class, Mapper\AdvancedDropdownMapper::class, Mapper\MegaMenuMapper::class, Mapper\AdvancedItemMapper::class] as $mapper) {
            $services->set($mapper)->autowire()->autoconfigure();
        }

        $builder->getDefinition(EventListener\MenuListener::class)
            ->addTag('kernel.event_listener', ['event' => \Symfony\Component\HttpKernel\KernelEvents::CONTROLLER]);

        if ($config['twig']['enabled']) {
            $services->set(Twig\MenuExtension::class)
                ->autowire()->autoconfigure()->lazy(true)
                ->tag('twig.extension');
            $path = $this->getPath();
            $container->extension('twig', ['paths' => [$path.'/templates' => 'SymkitMenu']], true);
        }

        if ($config['assets']['enabled']) {
            $path = $this->getPath();
            $container->extension('framework', ['asset_mapper' => ['paths' => [$path.'/assets/controllers' => 'menu']]], true);
        }

        if ($config['admin']['enabled']) {
            $services->set(Controller\Admin\Api\ItemsController::class)->autowire()->autoconfigure()->tag('controller.service_arguments');
            $services->set(Controller\Admin\MenuController::class)
                ->arg('$menuEntityClass', '%symkit_menu.entity.menu%')
                ->autowire()->autoconfigure()->tag('controller.service_arguments');
            $services->set(Controller\Admin\MenuItemController::class)
                ->arg('$menuEntityClass', '%symkit_menu.entity.menu%')
                ->arg('$menuItemEntityClass', '%symkit_menu.entity.menu_item%')
                ->autowire()->autoconfigure()->tag('controller.service_arguments');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Prepend is done conditionally in loadExtension based on config (twig.enabled, assets.enabled)
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new MenuListenerPass());
    }
}
