<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

/**
 * Populates page metadata from a domain object.
 *
 * Implementations are auto-tagged with `symkit_metadata.populator`.
 * Use this to automatically fill title, description, OG data, etc.
 * from entities or DTOs passed as the subject.
 */
interface MetadataPopulatorInterface
{
    /**
     * Whether this populator can handle the given subject.
     */
    public function supports(object $subject): bool;

    /**
     * Populates the builder with metadata derived from the subject.
     */
    public function populateMetadata(object $subject, PageContextBuilderInterface $builder): void;
}
