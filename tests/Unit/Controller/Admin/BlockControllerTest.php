<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Controller\Admin;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symkit\BuilderBundle\Controller\Admin\BlockController;
use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Form\BlockType;

final class BlockControllerTest extends TestCase
{
    private BlockController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        // BlockController overrides the parent constructor, only requiring $blockClass
        $this->controller = new BlockController(Block::class);
    }

    public function testGetEntityClassReturnsBlockClass(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'getEntityClass');
        $method->setAccessible(true);
        self::assertSame(Block::class, $method->invoke($this->controller));
    }

    public function testGetFormClassReturnsBlockType(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'getFormClass');
        $method->setAccessible(true);
        self::assertSame(BlockType::class, $method->invoke($this->controller));
    }

    public function testGetNewTemplateReturnsCrudTemplate(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'getNewTemplate');
        $method->setAccessible(true);
        self::assertSame('@SymkitCrud/crud/entity_form.html.twig', $method->invoke($this->controller));
    }

    public function testGetEditTemplateReturnsCrudTemplate(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'getEditTemplate');
        $method->setAccessible(true);
        self::assertSame('@SymkitCrud/crud/entity_form.html.twig', $method->invoke($this->controller));
    }

    public function testGetRoutePrefixReturnsAdminBlock(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'getRoutePrefix');
        $method->setAccessible(true);
        self::assertSame('admin_block', $method->invoke($this->controller));
    }

    public function testConfigureListFieldsReturnsExpectedKeys(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'configureListFields');
        $method->setAccessible(true);
        $fields = $method->invoke($this->controller);
        self::assertIsArray($fields);
        self::assertArrayHasKey('code', $fields);
        self::assertArrayHasKey('label', $fields);
        self::assertArrayHasKey('category', $fields);
        self::assertArrayHasKey('isActive', $fields);
        self::assertArrayHasKey('actions', $fields);
    }

    public function testConfigureSearchFieldsReturnsCodeAndLabel(): void
    {
        $method = new ReflectionMethod(BlockController::class, 'configureSearchFields');
        $method->setAccessible(true);
        $fields = $method->invoke($this->controller);
        self::assertIsArray($fields);
        self::assertContains('code', $fields);
        self::assertContains('label', $fields);
    }

    public function testCustomBlockClassIsUsed(): void
    {
        $controller = new BlockController(Exception::class);
        $method = new ReflectionMethod(BlockController::class, 'getEntityClass');
        $method->setAccessible(true);
        self::assertSame(Exception::class, $method->invoke($controller));
    }
}
