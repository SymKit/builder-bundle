<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Validator\Constraints\BlockContentSource;
use Symkit\BuilderBundle\Validator\Constraints\BlockContentSourceValidator;

final class BlockContentSourceValidatorTest extends TestCase
{
    private BlockContentSourceValidator $validator;

    /** @var \PHPUnit\Framework\MockObject\MockObject&ExecutionContextInterface */
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new BlockContentSourceValidator(Block::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testNoViolationWhenOnlyTemplate(): void
    {
        $block = new Block();
        $block->setCode('test');
        $block->setLabel('Test');
        $block->setTemplate('@SymkitBuilder/blocks/paragraph.html.twig');
        $block->setHtmlCode(null);

        $this->context->expects(self::never())->method('buildViolation');

        $this->validator->validate($block, new BlockContentSource());
    }

    public function testNoViolationWhenOnlyHtmlCode(): void
    {
        $block = new Block();
        $block->setCode('test');
        $block->setLabel('Test');
        $block->setTemplate(null);
        $block->setHtmlCode('<p>content</p>');

        $this->context->expects(self::never())->method('buildViolation');

        $this->validator->validate($block, new BlockContentSource());
    }

    public function testViolationWhenBothTemplateAndHtmlCode(): void
    {
        $block = new Block();
        $block->setCode('test');
        $block->setLabel('Test');
        $block->setTemplate('@template');
        $block->setHtmlCode('<p>html</p>');

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->method('atPath')->willReturnSelf();
        $builder->method('addViolation')->willReturnSelf();

        $this->context->expects(self::once())
            ->method('buildViolation')
            ->with('validation.block_content_source.both')
            ->willReturn($builder);

        $this->validator->validate($block, new BlockContentSource());
    }

    public function testViolationWhenNeitherTemplateNorHtmlCode(): void
    {
        $block = new Block();
        $block->setCode('test');
        $block->setLabel('Test');
        $block->setTemplate(null);
        $block->setHtmlCode(null);

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->method('atPath')->willReturnSelf();
        $builder->method('addViolation')->willReturnSelf();

        $this->context->expects(self::once())
            ->method('buildViolation')
            ->with('validation.block_content_source.neither')
            ->willReturn($builder);

        $this->validator->validate($block, new BlockContentSource());
    }

    public function testThrowsUnexpectedTypeExceptionWhenConstraintIsWrongType(): void
    {
        $constraint = $this->createMock(Constraint::class);
        $block = new Block();

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(BlockContentSource::class);

        $this->validator->validate($block, $constraint);
    }

    public function testThrowsUnexpectedValueExceptionWhenValueIsNotBlock(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(Block::class);

        $this->validator->validate(new stdClass(), new BlockContentSource());
    }
}
