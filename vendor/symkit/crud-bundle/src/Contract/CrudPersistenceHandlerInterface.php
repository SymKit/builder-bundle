<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Contract;

interface CrudPersistenceHandlerInterface
{
    public function persist(object $entity): void;

    public function update(object $entity): void;

    public function delete(object $entity): void;

    public function flush(): void;
}
