<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Component;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symkit\CrudBundle\Contract\CrudListProviderInterface;

final class CrudList
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $entityClass;

    /** @var array<string, array<string, mixed>> */
    #[LiveProp]
    public array $listFields = [];

    /** @var list<string> */
    #[LiveProp]
    public array $searchFields = [];

    /** @var array<string, mixed> */
    #[LiveProp(writable: true)]
    public array $filters = [];

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    #[LiveProp]
    public int $limit = 25;

    #[LiveProp]
    public ?string $sortBy = null;

    #[LiveProp]
    public string $sortDirection = 'asc';

    public function __construct(
        private readonly CrudListProviderInterface $entityProvider,
        int $defaultPageSize = 25,
    ) {
        $this->limit = $defaultPageSize;
    }

    /** @param array<string, mixed> $filters */
    #[LiveListener('filterUpdated')]
    public function onFilterUpdated(#[LiveArg] array $filters = []): void
    {
        $this->filters = $filters;
        $this->page = 1;
    }

    #[LiveAction]
    public function changeSort(#[LiveArg] string $column): void
    {
        if (!isset($this->listFields[$column]) || !($this->listFields[$column]['sortable'] ?? false)) {
            return;
        }

        $this->sortDirection = match (true) {
            $this->sortBy === $column => 'asc' === $this->sortDirection ? 'desc' : 'asc',
            default => 'asc',
        };
        $this->sortBy = $column;
    }

    /** @return Paginator<object> */
    public function getEntities(): Paginator
    {
        if (!\in_array(strtoupper($this->sortDirection), ['ASC', 'DESC'], true)) {
            $this->sortDirection = 'asc';
        }

        if (!$this->sortBy) {
            foreach ($this->listFields as $field => $config) {
                if ($config['sortable'] ?? false) {
                    $this->sortBy = $field;
                    break;
                }
            }
        }

        return $this->entityProvider->getEntities(
            $this->entityClass,
            $this->filters,
            $this->searchFields,
            $this->sortBy,
            $this->sortDirection,
            $this->page,
            $this->limit,
        );
    }

    public function getMinKey(): int
    {
        return ($this->page - 1) * $this->limit + 1;
    }

    public function getMaxKey(int $total): int
    {
        return min($this->page * $this->limit, $total);
    }
}
