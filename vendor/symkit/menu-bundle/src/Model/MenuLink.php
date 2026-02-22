<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

use Symkit\MenuBundle\Entity\MenuItem;

final class MenuLink extends AbstractMenuItem
{
    public function __construct(
        string $id,
        string $label,
        private readonly string $url,
        ?string $icon = null,
    ) {
        parent::__construct($id, $label, $icon);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return MenuItem::TYPE_LINK;
    }
}
