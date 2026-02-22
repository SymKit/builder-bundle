<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

use Symkit\MetadataBundle\Enum\OgType;
use Symkit\MetadataBundle\Enum\TwitterCard;
use Symkit\MetadataBundle\Model\PageContext;

/**
 * Builds page metadata context for the current request.
 *
 * Typically called from controller listeners, populators, or directly
 * in controllers to set SEO-related metadata for the page.
 * All setters are fluent and invalidate the cached context.
 */
interface PageContextBuilderInterface
{
    public function setTitle(?string $title): self;

    public function setDescription(?string $description): self;

    public function setOgImage(?string $ogImage): self;

    public function setOgType(OgType $ogType): self;

    public function setTwitterCard(TwitterCard $twitterCard): self;

    public function setCanonicalUrl(string $canonicalUrl): self;

    /**
     * @param string|null $robots Robots directive (e.g. "noindex, nofollow")
     */
    public function setRobots(?string $robots): self;

    public function setAuthor(?string $author): self;

    /**
     * @param array<string, mixed> $properties
     */
    public function setProperties(array $properties): self;

    /**
     * @param array<string, mixed> $properties
     */
    public function addProperties(array $properties): self;

    /**
     * Creates a new immutable PageContext from the current state.
     */
    public function build(): PageContext;
}
