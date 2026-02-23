<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Controller\Admin\Category;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symkit\BuilderBundle\Controller\Admin\Category\CategoryController;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Form\BlockCategoryType;

final class CategoryControllerTest extends TestCase
{
    private CategoryController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        // CategoryController overrides the parent constructor, only requiring $blockCategoryClass
        $this->controller = new CategoryController(BlockCategory::class);
    }

    public function testGetEntityClassReturnsBlockCategoryClass(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'getEntityClass');
        $method->setAccessible(true);
        self::assertSame(BlockCategory::class, $method->invoke($this->controller));
    }

    public function testGetFormClassReturnsBlockCategoryType(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'getFormClass');
        $method->setAccessible(true);
        self::assertSame(BlockCategoryType::class, $method->invoke($this->controller));
    }

    public function testGetNewTemplateReturnsCrudTemplate(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'getNewTemplate');
        $method->setAccessible(true);
        self::assertSame('@SymkitCrud/crud/entity_form.html.twig', $method->invoke($this->controller));
    }

    public function testGetEditTemplateReturnsCrudTemplate(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'getEditTemplate');
        $method->setAccessible(true);
        self::assertSame('@SymkitCrud/crud/entity_form.html.twig', $method->invoke($this->controller));
    }

    public function testGetRoutePrefixReturnsAdminBlockCategory(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'getRoutePrefix');
        $method->setAccessible(true);
        self::assertSame('admin_block_category', $method->invoke($this->controller));
    }

    public function testConfigureListFieldsReturnsExpectedKeys(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'configureListFields');
        $method->setAccessible(true);
        $fields = $method->invoke($this->controller);
        self::assertIsArray($fields);
        self::assertArrayHasKey('label', $fields);
        self::assertArrayHasKey('code', $fields);
        self::assertArrayHasKey('position', $fields);
        self::assertArrayHasKey('blocks', $fields);
        self::assertArrayHasKey('actions', $fields);
    }

    public function testConfigureSearchFieldsReturnsLabelAndCode(): void
    {
        $method = new ReflectionMethod(CategoryController::class, 'configureSearchFields');
        $method->setAccessible(true);
        $fields = $method->invoke($this->controller);
        self::assertIsArray($fields);
        self::assertContains('label', $fields);
        self::assertContains('code', $fields);
    }

    public function testCustomCategoryClassIsUsed(): void
    {
        $controller = new CategoryController(Exception::class);
        $method = new ReflectionMethod(CategoryController::class, 'getEntityClass');
        $method->setAccessible(true);
        self::assertSame(Exception::class, $method->invoke($controller));
    }
}
