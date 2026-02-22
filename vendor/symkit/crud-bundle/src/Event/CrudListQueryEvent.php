<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

final class CrudListQueryEvent extends Event
{
    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $filters
     * @param list<string>         $searchFields
     */
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly string $entityClass,
        private readonly array $filters,
        private readonly array $searchFields = [],
    ) {
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /** @return array<string, mixed> */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /** @return list<string> */
    public function getSearchFields(): array
    {
        return $this->searchFields;
    }
}
