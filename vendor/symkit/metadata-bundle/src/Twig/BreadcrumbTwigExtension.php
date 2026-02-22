<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Twig;

use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symkit\MetadataBundle\Model\BreadcrumbItem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BreadcrumbTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly BreadcrumbServiceInterface $breadcrumbService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('page_breadcrumbs', $this->getBreadcrumbs(...)),
        ];
    }

    /** @return list<BreadcrumbItem> */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbService->getItems();
    }
}
