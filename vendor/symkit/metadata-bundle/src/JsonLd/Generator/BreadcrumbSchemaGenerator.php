<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Generator;

use Spatie\SchemaOrg\Schema;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symkit\MetadataBundle\Contract\SchemaGeneratorInterface;

final readonly class BreadcrumbSchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private BreadcrumbServiceInterface $breadcrumbService,
    ) {
    }

    public function generate(): iterable
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $items = $this->breadcrumbService->getItems();
        if ([] === $items) {
            return;
        }

        $listItems = [];
        foreach ($items as $item) {
            $listItems[] = Schema::listItem()
                ->position($item->position)
                ->name($item->name)
                ->setProperty('item', $item->url);
        }

        yield Schema::breadcrumbList()
            ->setProperty('@id', $request->getUri().'#breadcrumb')
            ->itemListElement($listItems);
    }
}
