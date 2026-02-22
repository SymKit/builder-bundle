<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

use Spatie\SchemaOrg\BaseType;

/**
 * Generates JSON-LD schemas automatically on every request.
 *
 * Implementations are auto-tagged with `symkit_metadata.schema_generator`.
 * Built-in generators produce WebSite, WebPage, and BreadcrumbList schemas.
 * Custom generators are added to the @graph alongside built-in ones.
 */
interface SchemaGeneratorInterface
{
    /** @return iterable<BaseType> */
    public function generate(): iterable;
}
