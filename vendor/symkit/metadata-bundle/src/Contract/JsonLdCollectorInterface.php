<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

use Spatie\SchemaOrg\BaseType;

/**
 * Collects JSON-LD schema objects for the current request.
 *
 * Add schemas from controllers, populators, or services. The collector
 * accepts both SchemaDefinitionInterface DTOs (converted automatically)
 * and raw Spatie BaseType objects.
 * All collected schemas are merged into a single @graph at render time.
 */
interface JsonLdCollectorInterface
{
    public function add(SchemaDefinitionInterface|BaseType $schema): self;

    /** @return list<BaseType> */
    public function getSchemas(): array;
}
