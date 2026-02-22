<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Persistence\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symkit\CrudBundle\Contract\CrudPersistenceHandlerInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface;
use Symkit\CrudBundle\Enum\CrudEvents;
use Symkit\CrudBundle\Event\CrudEvent;

final readonly class CrudPersistenceManager implements CrudPersistenceManagerInterface
{
    public function __construct(
        private readonly CrudPersistenceHandlerInterface $handler,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function persist(object $entity, ?FormInterface $form = null, ?Request $request = null): void
    {
        $this->eventDispatcher->dispatch(new CrudEvent($entity, $form, $request), CrudEvents::PRE_PERSIST->value);

        $this->handler->persist($entity);
        $this->handler->flush();

        $this->eventDispatcher->dispatch(new CrudEvent($entity, $form, $request), CrudEvents::POST_PERSIST->value);
    }

    public function update(object $entity, ?FormInterface $form = null, ?Request $request = null): void
    {
        $this->eventDispatcher->dispatch(new CrudEvent($entity, $form, $request), CrudEvents::PRE_UPDATE->value);

        $this->handler->update($entity);
        $this->handler->flush();

        $this->eventDispatcher->dispatch(new CrudEvent($entity, $form, $request), CrudEvents::POST_UPDATE->value);
    }

    public function delete(object $entity, ?Request $request = null): void
    {
        $this->eventDispatcher->dispatch(new CrudEvent($entity, null, $request), CrudEvents::PRE_DELETE->value);

        $this->handler->delete($entity);
        $this->handler->flush();

        $this->eventDispatcher->dispatch(new CrudEvent($entity, null, $request), CrudEvents::POST_DELETE->value);
    }
}
