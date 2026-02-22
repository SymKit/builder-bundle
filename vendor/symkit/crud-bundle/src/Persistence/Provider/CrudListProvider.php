<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Persistence\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symkit\CrudBundle\Contract\CrudListProviderInterface;
use Symkit\CrudBundle\Enum\CrudEvents;
use Symkit\CrudBundle\Event\CrudListQueryEvent;

final readonly class CrudListProvider implements CrudListProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

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
    ): Paginator {
        /** @var class-string<object> $entityClass */
        $qb = $this->entityManager->getRepository($entityClass)->createQueryBuilder('e');

        $this->eventDispatcher->dispatch(new CrudListQueryEvent($qb, $entityClass, $filters, $searchFields), CrudEvents::LIST_QUERY->value);

        $searchValue = $filters['q'] ?? null;

        if ($searchValue && !empty($searchFields)) {
            /** @var string $searchString */
            $searchString = \is_string($searchValue) ? $searchValue : '';

            $orX = $qb->expr()->orX();
            foreach ($searchFields as $field) {
                $orX->add($qb->expr()->like("e.{$field}", ':query'));
            }
            $qb->andWhere($orX)->setParameter('query', '%'.$searchString.'%');
        }

        if ($sortBy) {
            $qb->orderBy('e.'.$sortBy, $sortDirection);
        }

        $dqlQuery = $qb->getQuery();

        /** @var Paginator<object> $paginator */
        $paginator = new Paginator($dqlQuery);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit)
        ;

        return $paginator;
    }
}
