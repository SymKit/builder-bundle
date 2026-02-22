<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

/**
 * Populates JSON-LD structured data from a domain object.
 *
 * Implementations are auto-tagged with `symkit_metadata.jsonld_populator`.
 * Use this to add schema.org structured data (Product, Article, etc.)
 * derived from entities or DTOs.
 */
interface JsonLdPopulatorInterface
{
    /**
     * Whether this populator can handle the given subject.
     */
    public function supports(object $subject): bool;

    /**
     * Adds JSON-LD schemas derived from the subject to the collector.
     */
    public function populateJsonLd(object $subject, JsonLdCollectorInterface $collector): void;
}
