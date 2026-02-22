<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Validator\Constraints;

use Symkit\BuilderBundle\Entity\Block;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class BlockContentSourceValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof BlockContentSource) {
            throw new UnexpectedTypeException($constraint, BlockContentSource::class);
        }

        if (!$value instanceof Block) {
            throw new UnexpectedValueException($value, Block::class);
        }

        $template = $value->getTemplate();
        $htmlCode = $value->getHtmlCode();

        $hasTemplate = !empty($template);
        $hasHtmlCode = !empty($htmlCode);

        if ($hasTemplate && $hasHtmlCode) {
            $this->context->buildViolation($constraint->message)
                ->atPath('template')
                ->addViolation()
            ;

            return;
        }

        if (!$hasTemplate && !$hasHtmlCode) {
            $this->context->buildViolation($constraint->neitherMessage)
                ->atPath('template')
                ->addViolation()
            ;
        }
    }
}
