<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class BlockContentSource extends Constraint
{
    public string $message = 'validation.block_content_source.both';
    public string $neitherMessage = 'validation.block_content_source.neither';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
