<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class BlockContentSource extends Constraint
{
    public string $message = 'You must provide either a Twig template path OR HTML code, but not both.';
    public string $neitherMessage = 'You must provide either a Twig template path or HTML code.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
