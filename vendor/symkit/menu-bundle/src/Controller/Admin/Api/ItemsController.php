<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Controller\Admin\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symkit\MenuBundle\Entity\Menu;
use Symkit\MenuBundle\Service\MenuItemChoicesProvider;

final class ItemsController
{
    public function __construct(
        private readonly MenuItemChoicesProvider $choicesProvider,
    ) {
    }

    public function __invoke(Menu $menu): JsonResponse
    {
        return new JsonResponse($this->choicesProvider->getChoices($menu));
    }
}
