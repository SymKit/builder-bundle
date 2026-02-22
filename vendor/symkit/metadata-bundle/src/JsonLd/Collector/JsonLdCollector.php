<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Collector;

use Spatie\SchemaOrg\BaseType;
use Symfony\Contracts\Service\ResetInterface;
use Symkit\MetadataBundle\Contract\JsonLdCollectorInterface;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final class JsonLdCollector implements JsonLdCollectorInterface, ResetInterface
{
    /** @var list<BaseType> */
    private array $schemas = [];

    public function add(SchemaDefinitionInterface|BaseType $schema): self
    {
        $this->schemas[] = $schema instanceof SchemaDefinitionInterface
            ? $schema->toSchemaOrg()
            : $schema;

        return $this;
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function reset(): void
    {
        $this->schemas = [];
    }
}
