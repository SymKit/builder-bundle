<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Service;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symkit\MenuBundle\Builder\MenuNavigationBuilderInterface;
use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Entity\Menu;
use Symkit\MenuBundle\Entity\MenuItem as MenuItemEntity;
use Symkit\MenuBundle\Repository\MenuRepository;

final readonly class MenuProvider
{
    /**
     * @param ServiceLocator<object> $builders
     */
    public function __construct(
        private ?MenuRepository $menuRepository,
        private ServiceLocator $builders,
        private MenuModelFactory $modelFactory,
    ) {
    }

    /** @return MenuItemInterface[] */
    public function getItems(string $menuName): array
    {
        // Priority 1: Database Menu (when Doctrine is enabled)
        if (null !== $this->menuRepository) {
            $menuEntity = $this->menuRepository->findOneBy(['code' => $menuName]);
            if ($menuEntity instanceof Menu) {
                return $this->buildFromEntity($menuEntity);
            }
        }

        // Priority 2: File-based Builder
        if ($this->builders->has($menuName)) {
            $builder = $this->builders->get($menuName);
            if ($builder instanceof MenuNavigationBuilderInterface) {
                return $builder->build();
            }
        }

        return [];
    }

    /** @return MenuItemInterface[] */
    private function buildFromEntity(Menu $menu): array
    {
        $items = [];
        $topLevelItems = $menu->getItems()->filter(static fn (MenuItemEntity $item) => null === $item->getParent());

        foreach ($topLevelItems as $entity) {
            $model = $this->modelFactory->createModel($entity);
            if ($model) {
                $items[] = $model;
            }
        }

        return $items;
    }
}
