<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Generator;

use Spatie\SchemaOrg\Schema;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Contract\PageContextProviderInterface;
use Symkit\MetadataBundle\Contract\SchemaGeneratorInterface;

final readonly class WebPageSchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private PageContextProviderInterface $contextProvider,
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
        $context = $this->contextProvider->getContext();

        yield Schema::webPage()
            ->setProperty('@id', $request->getUri().'#webpage')
            ->url($request->getUri())
            ->name($context->title)
            ->description($context->description)
            ->isPartOf(['@id' => $baseUri.'#website'])
            ->about(['@id' => $baseUri.'#organization'])
            ->breadcrumb(['@id' => $request->getUri().'#breadcrumb']);
    }
}
