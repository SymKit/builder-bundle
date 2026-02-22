<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Contract;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface CrudPersistenceManagerInterface
{
    public function persist(object $entity, ?FormInterface $form = null, ?Request $request = null): void;

    public function update(object $entity, ?FormInterface $form = null, ?Request $request = null): void;

    public function delete(object $entity, ?Request $request = null): void;
}
