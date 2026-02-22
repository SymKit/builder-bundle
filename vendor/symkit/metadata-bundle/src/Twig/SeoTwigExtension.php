<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Twig;

use Symkit\MetadataBundle\Contract\PageContextProviderInterface;
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;
use Symkit\MetadataBundle\Renderer\MetaTagRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SeoTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly PageContextProviderInterface $contextProvider,
        private readonly SiteInfoProviderInterface $siteInfoProvider,
        private readonly MetaTagRenderer $metaTagRenderer,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('page_title', $this->getTitle(...)),
            new TwigFunction('page_description', $this->getDescription(...)),
            new TwigFunction('page_site_name', $this->getSiteName(...)),
            new TwigFunction('page_site_description', $this->getSiteDescription(...)),
            new TwigFunction('page_default_og_image_url', $this->getDefaultOgImageUrl(...)),
            new TwigFunction('page_favicon_url', $this->getFaviconUrl(...)),
            new TwigFunction('page_apple_touch_icon_url', $this->getAppleTouchIconUrl(...)),
            new TwigFunction('page_android_icon_192_url', $this->getAndroidIcon192Url(...)),
            new TwigFunction('page_android_icon_512_url', $this->getAndroidIcon512Url(...)),
            new TwigFunction('page_metas', $this->renderMetas(...), ['is_safe' => ['html']]),
        ];
    }

    public function getTitle(): string
    {
        return $this->contextProvider->getContext()->title;
    }

    public function getDescription(): string
    {
        return $this->contextProvider->getContext()->description;
    }

    public function getSiteName(): string
    {
        return $this->siteInfoProvider->getWebsiteName();
    }

    public function getSiteDescription(): string
    {
        return $this->siteInfoProvider->getWebsiteDescription() ?? '';
    }

    public function getDefaultOgImageUrl(): ?string
    {
        return $this->contextProvider->getContext()->ogImage
            ?? $this->siteInfoProvider->getDefaultOgImage();
    }

    public function getFaviconUrl(): ?string
    {
        return $this->siteInfoProvider->getFavicon();
    }

    public function getAppleTouchIconUrl(): ?string
    {
        return $this->siteInfoProvider->getAppleTouchIcon();
    }

    public function getAndroidIcon192Url(): ?string
    {
        return $this->siteInfoProvider->getAndroidIcon192();
    }

    public function getAndroidIcon512Url(): ?string
    {
        return $this->siteInfoProvider->getAndroidIcon512();
    }

    public function renderMetas(): string
    {
        return $this->metaTagRenderer->render();
    }
}
