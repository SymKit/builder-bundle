<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symkit\MenuBundle\Entity\MenuItem;

final readonly class MenuItemPositionManager
{
    /**
     * @param class-string $menuItemEntityClass
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $menuItemEntityClass,
    ) {
    }

    public function reorderPositions(MenuItem $menuItem): void
    {
        $menu = $menuItem->getMenu();
        if (!$menu) {
            return;
        }

        $newPosition = $menuItem->getPosition();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('mi')
            ->from($this->menuItemEntityClass, 'mi')
            ->where('mi.menu = :menu')
            ->setParameter('menu', $menu)
            ->orderBy('mi.position', 'ASC')
            ->addOrderBy('mi.id', 'ASC')
        ;

        if ($menuItem->getId()) {
            $qb->andWhere('mi.id != :currentId')
                ->setParameter('currentId', $menuItem->getId())
            ;
        }

        /** @var list<MenuItem> $existingItems */
        $existingItems = $qb->getQuery()->getResult();

        /** @var list<MenuItem> $allItems */
        $allItems = [];
        $currentItemInserted = false;

        foreach ($existingItems as $item) {
            if (!$currentItemInserted && \count($allItems) === $newPosition) {
                $allItems[] = $menuItem;
                $currentItemInserted = true;
            }
            $allItems[] = $item;
        }

        if (!$currentItemInserted) {
            $allItems[] = $menuItem;
        }

        foreach ($allItems as $index => $item) {
            $item->setPosition($index);
        }

        $this->entityManager->flush();
    }
}
