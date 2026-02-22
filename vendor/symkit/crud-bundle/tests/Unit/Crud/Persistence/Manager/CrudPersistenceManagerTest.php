<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Persistence\Manager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symkit\CrudBundle\Contract\CrudPersistenceHandlerInterface;
use Symkit\CrudBundle\Enum\CrudEvents;
use Symkit\CrudBundle\Event\CrudEvent;
use Symkit\CrudBundle\Persistence\Manager\CrudPersistenceManager;

final class CrudPersistenceManagerTest extends TestCase
{
    public function testPersistDispatchesEventsAndDelegatesToHandler(): void
    {
        $entity = new \stdClass();
        $form = $this->createMock(FormInterface::class);
        $request = new Request();

        $handler = $this->createMock(CrudPersistenceHandlerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $callOrder = [];

        $dispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (CrudEvent $event, string $eventName) use ($entity, $form, $request, &$callOrder) {
                self::assertSame($entity, $event->getEntity());
                self::assertSame($form, $event->getForm());
                self::assertSame($request, $event->getRequest());
                $callOrder[] = $eventName;

                return $event;
            });

        $handler->expects(self::once())->method('persist')->with($entity)
            ->willReturnCallback(function () use (&$callOrder): void { $callOrder[] = 'persist'; });
        $handler->expects(self::once())->method('flush')
            ->willReturnCallback(function () use (&$callOrder): void { $callOrder[] = 'flush'; });

        $manager = new CrudPersistenceManager($handler, $dispatcher);
        $manager->persist($entity, $form, $request);

        self::assertSame([
            CrudEvents::PRE_PERSIST->value,
            'persist',
            'flush',
            CrudEvents::POST_PERSIST->value,
        ], $callOrder);
    }

    public function testPersistWorksWithoutFormAndRequest(): void
    {
        $entity = new \stdClass();

        $handler = $this->createMock(CrudPersistenceHandlerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (CrudEvent $event, string $eventName) {
                self::assertNull($event->getForm());
                self::assertNull($event->getRequest());

                return $event;
            });

        $handler->expects(self::once())->method('persist');
        $handler->expects(self::once())->method('flush');

        $manager = new CrudPersistenceManager($handler, $dispatcher);
        $manager->persist($entity);
    }

    public function testUpdateDispatchesEventsAndDelegatesToHandler(): void
    {
        $entity = new \stdClass();
        $form = $this->createMock(FormInterface::class);
        $request = new Request();

        $handler = $this->createMock(CrudPersistenceHandlerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $callOrder = [];

        $dispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (CrudEvent $event, string $eventName) use (&$callOrder) {
                $callOrder[] = $eventName;

                return $event;
            });

        $handler->expects(self::once())->method('update')->with($entity)
            ->willReturnCallback(function () use (&$callOrder): void { $callOrder[] = 'update'; });
        $handler->expects(self::once())->method('flush')
            ->willReturnCallback(function () use (&$callOrder): void { $callOrder[] = 'flush'; });

        $manager = new CrudPersistenceManager($handler, $dispatcher);
        $manager->update($entity, $form, $request);

        self::assertSame([
            CrudEvents::PRE_UPDATE->value,
            'update',
            'flush',
            CrudEvents::POST_UPDATE->value,
        ], $callOrder);
    }

    public function testDeleteDispatchesEventsAndDelegatesToHandler(): void
    {
        $entity = new \stdClass();
        $request = new Request();

        $handler = $this->createMock(CrudPersistenceHandlerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $callOrder = [];

        $dispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (CrudEvent $event, string $eventName) use ($entity, $request, &$callOrder) {
                self::assertSame($entity, $event->getEntity());
                self::assertNull($event->getForm());
                self::assertSame($request, $event->getRequest());
                $callOrder[] = $eventName;

                return $event;
            });

        $handler->expects(self::once())->method('delete')->with($entity)
            ->willReturnCallback(function () use (&$callOrder): void { $callOrder[] = 'delete'; });
        $handler->expects(self::once())->method('flush')
            ->willReturnCallback(function () use (&$callOrder): void { $callOrder[] = 'flush'; });

        $manager = new CrudPersistenceManager($handler, $dispatcher);
        $manager->delete($entity, $request);

        self::assertSame([
            CrudEvents::PRE_DELETE->value,
            'delete',
            'flush',
            CrudEvents::POST_DELETE->value,
        ], $callOrder);
    }

    public function testDeleteWorksWithoutRequest(): void
    {
        $entity = new \stdClass();

        $handler = $this->createMock(CrudPersistenceHandlerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (CrudEvent $event) {
                self::assertNull($event->getRequest());

                return $event;
            });

        $handler->expects(self::once())->method('delete');
        $handler->expects(self::once())->method('flush');

        $manager = new CrudPersistenceManager($handler, $dispatcher);
        $manager->delete($entity);
    }
}
