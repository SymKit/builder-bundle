<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Persistence\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceHandlerInterface;

final readonly class DoctrinePersistenceHandler implements CrudPersistenceHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(object $entity): void
    {
        $this->entityManager->persist($entity);
    }

    public function update(object $entity): void
    {
        // For Doctrine, update is implicit upon flush if entity is tracked,
        // but this method can be used for explicit operations if needed.
    }

    public function delete(object $entity): void
    {
        $this->entityManager->remove($entity);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
