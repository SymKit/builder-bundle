<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Listener;

use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * @template T of object
 */
trait ResolveControllerAttributeTrait
{
    /**
     * @param class-string<T> $attributeClass
     *
     * @return T|null
     */
    private function resolveAttribute(ControllerEvent $event, string $attributeClass): ?object
    {
        $controller = $event->getController();

        if (\is_array($controller) && \is_object($controller[0]) && \is_string($controller[1])) {
            $reflection = new ReflectionMethod($controller[0], $controller[1]);
        } elseif (\is_object($controller) && method_exists($controller, '__invoke')) {
            $reflection = new ReflectionMethod($controller, '__invoke');
        } else {
            return null;
        }

        $attributes = $reflection->getAttributes($attributeClass);

        if ([] !== $attributes) {
            return $attributes[0]->newInstance();
        }

        $classAttributes = $reflection->getDeclaringClass()->getAttributes($attributeClass);

        if ([] !== $classAttributes) {
            return $classAttributes[0]->newInstance();
        }

        return null;
    }
}
