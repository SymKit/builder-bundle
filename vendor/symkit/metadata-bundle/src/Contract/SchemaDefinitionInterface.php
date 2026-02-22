<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

use Spatie\SchemaOrg\BaseType;

/**
 * A DTO that can be converted to a Spatie Schema.org type.
 *
 * Implement this on immutable value objects (e.g. ArticleSchema,
 * ProductSchema) to provide a typed, framework-agnostic way to
 * define structured data. Pass instances to JsonLdCollectorInterface.
 */
interface SchemaDefinitionInterface
{
    public function toSchemaOrg(): BaseType;
}
