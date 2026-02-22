<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Persistence\Handler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symkit\CrudBundle\Persistence\Handler\DoctrinePersistenceHandler;

final class DoctrinePersistenceHandlerTest extends TestCase
{
    public function testPersistDelegatesToEntityManager(): void
    {
        $entity = new \stdClass();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($entity);

        $handler = new DoctrinePersistenceHandler($em);
        $handler->persist($entity);
    }

    public function testUpdateIsNoOp(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('remove');
        $em->expects(self::never())->method('flush');

        $handler = new DoctrinePersistenceHandler($em);
        $handler->update(new \stdClass());
    }

    public function testDeleteDelegatesToEntityManager(): void
    {
        $entity = new \stdClass();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('remove')->with($entity);

        $handler = new DoctrinePersistenceHandler($em);
        $handler->delete($entity);
    }

    public function testFlushDelegatesToEntityManager(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $handler = new DoctrinePersistenceHandler($em);
        $handler->flush();
    }
}
