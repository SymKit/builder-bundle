<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Enum;

use PHPUnit\Framework\TestCase;
use Symkit\CrudBundle\Enum\CrudEvents;

final class CrudEventsTest extends TestCase
{
    public function testCasesHaveExpectedValues(): void
    {
        self::assertSame('crud.pre_persist', CrudEvents::PRE_PERSIST->value);
        self::assertSame('crud.post_persist', CrudEvents::POST_PERSIST->value);
        self::assertSame('crud.pre_update', CrudEvents::PRE_UPDATE->value);
        self::assertSame('crud.post_update', CrudEvents::POST_UPDATE->value);
        self::assertSame('crud.pre_delete', CrudEvents::PRE_DELETE->value);
        self::assertSame('crud.post_delete', CrudEvents::POST_DELETE->value);
        self::assertSame('crud.list_query', CrudEvents::LIST_QUERY->value);
    }

    public function testEnumCasesCount(): void
    {
        self::assertCount(7, CrudEvents::cases());
    }
}
