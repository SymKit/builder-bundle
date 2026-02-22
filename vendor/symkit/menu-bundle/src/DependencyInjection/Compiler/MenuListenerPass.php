<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\DependencyInjection\Compiler;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MenuBundle\EventListener\MenuListener;

final class MenuListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(MenuListener::class)) {
            return;
        }

        $menuMetadata = [];
        $controllerServices = $container->findTaggedServiceIds('controller.service_arguments');

        foreach ($controllerServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(ActiveMenu::class);
                if (empty($attributes)) {
                    continue;
                }

                $methodName = $method->getName();

                $configs = [];
                foreach ($attributes as $attribute) {
                    /** @var ActiveMenu $instance */
                    $instance = $attribute->newInstance();
                    $configs[] = [
                        'menuName' => $instance->menuName,
                        'itemId' => $instance->itemId,
                    ];
                }

                // Register with full name (Class::method)
                $menuMetadata["{$id}::{$methodName}"] = $configs;

                // If it's __invoke, also register with just the service ID (standard Symfony _controller for invokables)
                if ('__invoke' === $methodName) {
                    $menuMetadata[$id] = $configs;
                }
            }
        }

        $definition = $container->getDefinition(MenuListener::class);
        $definition->setArgument('$menuMetadata', $menuMetadata);
    }
}
