<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\EventListener;

use Symkit\CrudBundle\Event\CrudEvent;
use Symkit\MenuBundle\Entity\MenuItem;
use Symkit\MenuBundle\Service\MenuItemPositionManager;

final readonly class MenuItemPositionListener
{
    public function __construct(
        private MenuItemPositionManager $positionManager,
    ) {
    }

    public function onCrudEvent(CrudEvent $event): void
    {
        $menuItem = $event->getEntity();

        if (!$menuItem instanceof MenuItem) {
            return;
        }

        $this->positionManager->reorderPositions($menuItem);
    }
}
