<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

use Symkit\MenuBundle\Entity\MenuItem;

final class MenuMega extends AbstractMenuItem
{
    /**
     * @param MegaMenuSection[] $sections
     * @param MegaMenuArticle[] $recentArticles
     */
    public function __construct(
        string $id,
        string $label,
        private readonly array $sections,
        private readonly array $recentArticles = [],
        ?string $icon = null,
    ) {
        parent::__construct($id, $label, $icon);
    }

    /** @return MegaMenuSection[] */
    public function getSections(): array
    {
        return $this->sections;
    }

    /** @return MegaMenuArticle[] */
    public function getRecentArticles(): array
    {
        return $this->recentArticles;
    }

    public function getType(): string
    {
        return MenuItem::TYPE_MEGA;
    }
}
