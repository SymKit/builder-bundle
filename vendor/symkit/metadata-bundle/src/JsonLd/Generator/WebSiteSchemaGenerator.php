<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Generator;

use Spatie\SchemaOrg\Schema;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Contract\PageContextProviderInterface;
use Symkit\MetadataBundle\Contract\SchemaGeneratorInterface;
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;

final readonly class WebSiteSchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private PageContextProviderInterface $contextProvider,
        private SiteInfoProviderInterface $siteInfoProvider,
    ) {
    }

    public function generate(): iterable
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $baseUrl = $request->getSchemeAndHttpHost();
        $baseUri = $baseUrl.'/';

        yield Schema::webSite()
            ->setProperty('@id', $baseUri.'#website')
            ->url($baseUrl)
            ->name($this->siteInfoProvider->getWebsiteName())
            ->description($this->siteInfoProvider->getWebsiteDescription() ?? '')
            ->publisher(['@id' => $baseUri.'#organization']);

        $context = $this->contextProvider->getContext();
        $ogImage = $context->ogImage ?? $this->siteInfoProvider->getDefaultOgImage();

        $organization = Schema::organization()
            ->setProperty('@id', $baseUri.'#organization')
            ->name($this->siteInfoProvider->getWebsiteName());

        if (null !== $ogImage) {
            $organization->logo(Schema::imageObject()->url($ogImage));
        }

        yield $organization;
    }
}
