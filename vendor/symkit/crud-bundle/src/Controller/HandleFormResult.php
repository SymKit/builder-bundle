<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Controller;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Response;

/**
 * Value object returned by AbstractCrudController::handleForm().
 *
 * Carries either a redirect Response (on successful submit) or the
 * FormView to render, avoiding the need to create the form twice.
 */
final readonly class HandleFormResult
{
    public function __construct(
        public ?Response $redirect,
        public FormView $formView,
    ) {
    }

    public function isRedirect(): bool
    {
        return null !== $this->redirect;
    }
}
