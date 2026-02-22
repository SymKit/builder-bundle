<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

use Symkit\MetadataBundle\Model\BreadcrumbItem;

/**
 * Manages the breadcrumb trail for the current request.
 *
 * Call initialize() with a context to load base items from the
 * corresponding BreadcrumbBuilderInterface, then add() page-specific
 * items. Items are returned in insertion order with auto-incremented positions.
 */
interface BreadcrumbServiceInterface
{
    /**
     * Initializes the breadcrumb trail by invoking the builder for the given context.
     * No-op if already initialized.
     */
    public function initialize(string $context = 'website'): void;

    /**
     * Whether the given route is the root of the specified context.
     */
    public function isRootRoute(string $route, string $context = 'website'): bool;

    public function add(string $name, string $url): self;

    /** @return list<BreadcrumbItem> */
    public function getItems(): array;
}
