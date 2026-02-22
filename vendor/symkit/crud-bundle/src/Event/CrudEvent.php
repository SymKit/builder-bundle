<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base event dispatched during CRUD lifecycle operations.
 *
 * This class is intentionally non-final so that consumers can extend it
 * with domain-specific data (e.g. extra context, computed values) while
 * reusing the same event listeners infrastructure.
 */
class CrudEvent extends Event
{
    public function __construct(
        private readonly object $entity,
        private readonly ?FormInterface $form = null,
        private readonly ?Request $request = null,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
