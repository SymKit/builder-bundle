<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Integration\Stub;

use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;

final readonly class StubSiteInfoProvider implements SiteInfoProviderInterface
{
    public function getWebsiteName(): string
    {
        return 'Test Site';
    }

    public function getWebsiteDescription(): ?string
    {
        return 'A test site';
    }

    public function getDefaultOgImage(): ?string
    {
        return null;
    }

    public function getFavicon(): ?string
    {
        return null;
    }

    public function getAppleTouchIcon(): ?string
    {
        return null;
    }

    public function getAndroidIcon192(): ?string
    {
        return null;
    }

    public function getAndroidIcon512(): ?string
    {
        return null;
    }
}
