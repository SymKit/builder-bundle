<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Persistence\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symkit\CrudBundle\Enum\CrudEvents;
use Symkit\CrudBundle\Event\CrudListQueryEvent;
use Symkit\CrudBundle\Persistence\Provider\CrudListProvider;

final class CrudListProviderTest extends TestCase
{
    public function testGetEntitiesBuildsQueryWithSort(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->expects(self::once())
            ->method('orderBy')
            ->with('e.name', 'asc');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->with('e')->willReturn($qb);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(CrudListQueryEvent::class),
                CrudEvents::LIST_QUERY->value,
            );

        $provider = new CrudListProvider($entityManager, $dispatcher);
        $provider->getEntities(
            'App\\Entity\\Post',
            [],
            [],
            'name',
            'asc',
            2,
            10,
        );
    }

    public function testGetEntitiesAppliesSearchFilter(): void
    {
        $orx = $this->createMock(Orx::class);

        $comparison = $this->createMock(Comparison::class);

        $expr = $this->createMock(Expr::class);
        $expr->method('orX')->willReturn($orx);
        $expr->method('like')->willReturn($comparison);

        $query = $this->createMock(Query::class);
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('expr')->willReturn($expr);
        $qb->method('getQuery')->willReturn($query);
        $qb->expects(self::once())
            ->method('andWhere')
            ->with($orx)
            ->willReturnSelf();
        $qb->expects(self::once())
            ->method('setParameter')
            ->with('query', '%test%')
            ->willReturnSelf();

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->with('e')->willReturn($qb);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        $provider = new CrudListProvider($entityManager, $dispatcher);
        $provider->getEntities(
            'App\\Entity\\Post',
            ['q' => 'test'],
            ['title', 'body'],
            null,
            'asc',
            1,
            25,
        );
    }

    public function testGetEntitiesWithoutSortBySkipsOrderBy(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->expects(self::never())->method('orderBy');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->with('e')->willReturn($qb);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        $provider = new CrudListProvider($entityManager, $dispatcher);
        $provider->getEntities(
            'App\\Entity\\Post',
            [],
            [],
            null,
            'asc',
            1,
            10,
        );
    }

    public function testGetEntitiesDispatchesListQueryEventWithCorrectData(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->with('e')->willReturn($qb);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (CrudListQueryEvent $event): bool {
                    return 'App\\Entity\\Post' === $event->getEntityClass()
                        && ['status' => 'active'] === $event->getFilters();
                }),
                CrudEvents::LIST_QUERY->value,
            );

        $provider = new CrudListProvider($entityManager, $dispatcher);
        $provider->getEntities(
            'App\\Entity\\Post',
            ['status' => 'active'],
            [],
            null,
            'asc',
            1,
            10,
        );
    }

    public function testGetEntitiesDoesNotApplySearchWhenNoSearchFields(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->expects(self::never())->method('andWhere');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->with('e')->willReturn($qb);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        $provider = new CrudListProvider($entityManager, $dispatcher);
        $provider->getEntities(
            'App\\Entity\\Post',
            ['q' => 'search'],
            [],
            null,
            'asc',
            1,
            10,
        );
    }
}
