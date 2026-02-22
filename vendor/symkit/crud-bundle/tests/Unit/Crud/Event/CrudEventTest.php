<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symkit\CrudBundle\Event\CrudEvent;

final class CrudEventTest extends TestCase
{
    public function testGetEntityReturnsConstructedEntity(): void
    {
        $entity = new \stdClass();
        $event = new CrudEvent($entity);

        self::assertSame($entity, $event->getEntity());
    }

    public function testGetFormAndGetRequestDefaultToNull(): void
    {
        $event = new CrudEvent(new \stdClass());

        self::assertNull($event->getForm());
        self::assertNull($event->getRequest());
    }

    public function testGetFormReturnsProvidedForm(): void
    {
        $entity = new \stdClass();
        $form = self::createMock(FormInterface::class);
        $event = new CrudEvent($entity, $form);

        self::assertSame($form, $event->getForm());
    }

    public function testGetRequestReturnsProvidedRequest(): void
    {
        $entity = new \stdClass();
        $request = new Request();
        $event = new CrudEvent($entity, null, $request);

        self::assertSame($request, $event->getRequest());
    }

    public function testAllGettersReturnProvidedValues(): void
    {
        $entity = new \stdClass();
        $form = self::createMock(FormInterface::class);
        $request = new Request();
        $event = new CrudEvent($entity, $form, $request);

        self::assertSame($entity, $event->getEntity());
        self::assertSame($form, $event->getForm());
        self::assertSame($request, $event->getRequest());
    }
}
