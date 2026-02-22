<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symkit\MetadataBundle\Model\PageContext;

final class PageContextEvent extends Event
{
    public function __construct(
        private readonly PageContext $context,
        private readonly ?object $subject = null,
    ) {
    }

    public function getContext(): PageContext
    {
        return $this->context;
    }

    public function getSubject(): ?object
    {
        return $this->subject;
    }
}
