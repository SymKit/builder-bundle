<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Twig\Component;

use PHPUnit\Framework\TestCase;
use Symkit\CrudBundle\Twig\Component\DeleteForm;

final class DeleteFormTest extends TestCase
{
    public function testGetCsrfTokenNameConcatenatesTokenIdAndEntityId(): void
    {
        $component = new DeleteForm();
        $component->csrfTokenId = 'delete';
        $component->entityId = 42;

        self::assertSame('delete42', $component->getCsrfTokenName());
    }

    public function testGetCsrfTokenNameWithStringEntityId(): void
    {
        $component = new DeleteForm();
        $component->csrfTokenId = 'remove';
        $component->entityId = 'abc-123';

        self::assertSame('removeabc-123', $component->getCsrfTokenName());
    }

    public function testDefaultValues(): void
    {
        $component = new DeleteForm();

        self::assertSame([], $component->routeParams);
        self::assertSame('delete-form', $component->formId);
        self::assertSame('crud.component.delete_form.confirm', $component->confirmMessage);
        self::assertSame('delete', $component->csrfTokenId);
    }
}
