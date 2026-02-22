<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

/**
 * Builds the base breadcrumb trail for a given context.
 *
 * Implementations are auto-tagged with `symkit_metadata.breadcrumb_builder`
 * and registered in a service locator keyed by context name (e.g. "website",
 * "admin"). The builder is called once per request during initialization
 * to populate root-level breadcrumb items before page-specific items are added.
 */
interface BreadcrumbBuilderInterface
{
    /**
     * Populates the breadcrumb service with base items for this context.
     */
    public function build(BreadcrumbServiceInterface $service): void;

    /**
     * Whether the given route is the root of this breadcrumb context.
     * Used to determine if the breadcrumb trail should stop.
     */
    public function isRootRoute(string $route): bool;
}
