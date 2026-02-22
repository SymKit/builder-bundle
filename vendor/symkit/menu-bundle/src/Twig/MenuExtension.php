<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Twig;

use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Manager\MenuManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly MenuManager $menuManager,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_menu', [$this, 'getMenu']),
        ];
    }

    /** @return MenuItemInterface[] */
    public function getMenu(string $name): array
    {
        return $this->menuManager->getItems($name);
    }
}
