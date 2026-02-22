<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symkit\MenuBundle\Manager\MenuManager;

final readonly class MenuListener
{
    /**
     * @param array<string, array<array{menuName: string, itemId: string}>> $menuMetadata
     */
    public function __construct(
        private MenuManager $menuManager,
        private array $menuMetadata = [],
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getRequest()->attributes->get('_controller');

        if (\is_string($controller) && isset($this->menuMetadata[$controller])) {
            $this->menuManager->handleMetadata($this->menuMetadata[$controller]);
        }
    }
}
