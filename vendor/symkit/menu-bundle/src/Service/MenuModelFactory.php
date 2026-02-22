<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Service;

use Symkit\MenuBundle\Contract\MenuItemEntityInterface;
use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Contract\MenuModelFactoryInterface;
use Symkit\MenuBundle\Mapper\MenuItemMapperInterface;

final readonly class MenuModelFactory implements MenuModelFactoryInterface
{
    /**
     * @param iterable<MenuItemMapperInterface> $mappers
     */
    public function __construct(
        private iterable $mappers,
    ) {
    }

    public function createModel(MenuItemEntityInterface $entity): ?MenuItemInterface
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($entity)) {
                return $mapper->map($entity, $this);
            }
        }

        return null;
    }
}
