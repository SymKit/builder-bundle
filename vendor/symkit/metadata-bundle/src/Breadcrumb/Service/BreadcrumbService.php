<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Breadcrumb\Service;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ResetInterface;
use Symkit\MetadataBundle\Contract\BreadcrumbBuilderInterface;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symkit\MetadataBundle\Model\BreadcrumbItem;

final class BreadcrumbService implements BreadcrumbServiceInterface, ResetInterface
{
    /** @var list<BreadcrumbItem> */
    private array $items = [];
    private bool $initialized = false;

    /**
     * @param ServiceLocator<BreadcrumbBuilderInterface> $builders
     */
    public function __construct(
        private readonly ServiceLocator $builders,
    ) {
    }

    public function initialize(string $context = 'website'): void
    {
        if ($this->initialized) {
            return;
        }

        if ($this->builders->has($context)) {
            /** @var BreadcrumbBuilderInterface $builder */
            $builder = $this->builders->get($context);
            $builder->build($this);
        }

        $this->initialized = true;
    }

    public function isRootRoute(string $route, string $context = 'website'): bool
    {
        if ($this->builders->has($context)) {
            /** @var BreadcrumbBuilderInterface $builder */
            $builder = $this->builders->get($context);

            return $builder->isRootRoute($route);
        }

        return false;
    }

    public function add(string $name, string $url): self
    {
        $this->items[] = new BreadcrumbItem($name, $url, \count($this->items) + 1);

        return $this;
    }

    public function getItems(): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->items;
    }

    public function reset(): void
    {
        $this->items = [];
        $this->initialized = false;
    }
}
