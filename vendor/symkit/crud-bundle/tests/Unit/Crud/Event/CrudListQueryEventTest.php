<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Event;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symkit\CrudBundle\Event\CrudListQueryEvent;

final class CrudListQueryEventTest extends TestCase
{
    public function testGettersReturnConstructedValues(): void
    {
        $qb = $this->createMock(QueryBuilder::class);
        $event = new CrudListQueryEvent($qb, 'App\Entity\Foo', ['q' => 'search']);

        self::assertSame($qb, $event->getQueryBuilder());
        self::assertSame('App\Entity\Foo', $event->getEntityClass());
        self::assertSame(['q' => 'search'], $event->getFilters());
    }
}
