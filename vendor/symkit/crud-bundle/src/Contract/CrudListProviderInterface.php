<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Contract;

use Doctrine\ORM\Tools\Pagination\Paginator;

interface CrudListProviderInterface
{
    /** @param array<string, mixed> $filters
     * @param list<string> $searchFields
     *
     * @return Paginator<object>
     */
    public function getEntities(
        string $entityClass,
        array $filters,
        array $searchFields,
        ?string $sortBy,
        string $sortDirection,
        int $page,
        int $limit,
    ): Paginator;
}
