<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Service;

use Symkit\MetadataBundle\Contract\JsonLdCollectorInterface;
use Symkit\MetadataBundle\Contract\SchemaGeneratorInterface;

final readonly class JsonLdService
{
    /**
     * @param iterable<SchemaGeneratorInterface> $generators
     */
    public function __construct(
        private iterable $generators,
        private JsonLdCollectorInterface $collector,
    ) {
    }

    public function generate(): string
    {
        $schemas = [];

        foreach ($this->generators as $generator) {
            foreach ($generator->generate() as $schema) {
                $schemas[] = $schema;
            }
        }

        foreach ($this->collector->getSchemas() as $schema) {
            $schemas[] = $schema;
        }

        if ([] === $schemas) {
            return '';
        }

        $graph = array_map(
            static fn (object $schema): array => array_diff_key($schema->toArray(), ['@context' => true]),
            $schemas,
        );

        return (string) json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_PRETTY_PRINT,
        );
    }
}
