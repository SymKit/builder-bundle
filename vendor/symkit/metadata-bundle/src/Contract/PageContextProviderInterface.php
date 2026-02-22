<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

use Symkit\MetadataBundle\Model\PageContext;

/**
 * Provides the current page metadata context (read-only access).
 *
 * Inject this interface in renderers and Twig extensions that need
 * to read the page context without modifying it.
 * The returned context is cached until the builder state changes.
 */
interface PageContextProviderInterface
{
    public function getContext(): PageContext;
}
