<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use stdClass;
use Symkit\MetadataBundle\Event\PageContextEvent;
use Symkit\MetadataBundle\Model\PageContext;

final class PageContextEventTest extends TestCase
{
    public function testGetContext(): void
    {
        $context = new PageContext(title: 'Test');
        $event = new PageContextEvent($context);

        self::assertSame($context, $event->getContext());
    }

    public function testGetSubjectDefault(): void
    {
        $context = new PageContext();
        $event = new PageContextEvent($context);

        self::assertNull($event->getSubject());
    }

    public function testGetSubjectWithObject(): void
    {
        $context = new PageContext();
        $subject = new stdClass();
        $event = new PageContextEvent($context, $subject);

        self::assertSame($subject, $event->getSubject());
    }

    public function testExtendsSymfonyEvent(): void
    {
        $context = new PageContext();
        $event = new PageContextEvent($context);

        self::assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }
}
