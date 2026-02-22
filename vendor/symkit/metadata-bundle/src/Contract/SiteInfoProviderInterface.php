<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Contract;

/**
 * Provides global site-level information for meta tag rendering.
 *
 * The host application must implement this interface and register
 * it as a service. Configure the service ID via the
 * `symkit_metadata.site_info_provider` option.
 */
interface SiteInfoProviderInterface
{
    public function getWebsiteName(): string;

    public function getWebsiteDescription(): ?string;

    /**
     * Default Open Graph image URL used when no page-specific image is set.
     */
    public function getDefaultOgImage(): ?string;

    public function getFavicon(): ?string;

    public function getAppleTouchIcon(): ?string;

    public function getAndroidIcon192(): ?string;

    public function getAndroidIcon512(): ?string;
}
