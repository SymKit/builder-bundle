<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class BlockContentSourceValidator extends ConstraintValidator
{
    /**
     * @param class-string $blockClass
     */
    public function __construct(
        private readonly string $blockClass,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof BlockContentSource) {
            throw new UnexpectedTypeException($constraint, BlockContentSource::class);
        }

        if (!\is_object($value) || !is_a($value, $this->blockClass)) {
            throw new UnexpectedValueException($value, $this->blockClass);
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
