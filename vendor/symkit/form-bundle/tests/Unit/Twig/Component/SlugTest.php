<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Twig\Component;

use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symkit\FormBundle\Twig\Component\Slug;

final class SlugTest extends TestCase
{
    private Slug $component;

    protected function setUp(): void
    {
        $this->component = new Slug(new AsciiSlugger());
    }

    public function testMountWithNullValueKeepsAutoUpdateEnabled(): void
    {
        $this->component->mount(null);

        self::assertNull($this->component->value);
        self::assertTrue($this->component->autoUpdate);
    }

    public function testMountWithExistingValueDisablesAutoUpdate(): void
    {
        $this->component->mount('existing-slug');

        self::assertSame('existing-slug', $this->component->value);
        self::assertFalse($this->component->autoUpdate);
    }

    public function testGenerateSlugCreatesSlugFromSourceValue(): void
    {
        $this->component->mount(null);
        $this->component->isLocked = true;
        $this->component->autoUpdate = true;
        $this->component->sourceValue = 'Hello World';

        $this->component->generateSlug();

        self::assertSame('hello-world', $this->component->value);
    }

    public function testGenerateSlugDoesNothingWhenUnlocked(): void
    {
        $this->component->mount(null);
        $this->component->isLocked = false;
        $this->component->autoUpdate = true;
        $this->component->sourceValue = 'Hello World';

        $this->component->generateSlug();

        self::assertNull($this->component->value);
    }

    public function testGenerateSlugDoesNothingWhenAutoUpdateDisabled(): void
    {
        $this->component->mount(null);
        $this->component->isLocked = true;
        $this->component->autoUpdate = false;
        $this->component->sourceValue = 'Hello World';

        $this->component->generateSlug();

        self::assertNull($this->component->value);
    }

    public function testGenerateSlugDoesNotOverwriteValueWhenSourceIsEmpty(): void
    {
        $this->component->mount(null);
        $this->component->value = 'existing-value';
        $this->component->isLocked = true;
        $this->component->autoUpdate = true;
        $this->component->sourceValue = '';

        $this->component->generateSlug();

        self::assertSame('existing-value', $this->component->value);
    }

    public function testToggleLockTogglesIsLocked(): void
    {
        $this->component->mount(null);
        self::assertTrue($this->component->isLocked);

        $this->component->toggleLock();
        self::assertFalse($this->component->isLocked);

        $this->component->toggleLock();
        self::assertTrue($this->component->isLocked);
    }

    public function testToggleLockWhenRelockingReEnablesAutoUpdateAndGeneratesSlug(): void
    {
        $this->component->mount(null);
        $this->component->isLocked = false;
        $this->component->autoUpdate = false;
        $this->component->sourceValue = 'My Title';

        $this->component->toggleLock();

        self::assertTrue($this->component->isLocked);
        self::assertTrue($this->component->autoUpdate);
        self::assertSame('my-title', $this->component->value);
    }

    public function testOnValueUpdatedSlugifiesManualInput(): void
    {
        $this->component->mount(null);
        $this->component->value = 'My Custom Slug';

        $this->component->onValueUpdated();

        self::assertSame('my-custom-slug', $this->component->value);
    }

    public function testOnValueUpdatedDoesNothingWhenValueIsNull(): void
    {
        $this->component->mount(null);
        $this->component->value = null;

        $this->component->onValueUpdated();

        self::assertNull($this->component->value);
    }

    public function testOnValueUpdatedDoesNothingWhenValueIsEmpty(): void
    {
        $this->component->mount(null);
        $this->component->value = '';

        $this->component->onValueUpdated();

        self::assertSame('', $this->component->value);
    }

    public function testGenerateSlugHandlesSpecialCharacters(): void
    {
        $this->component->mount(null);
        $this->component->isLocked = true;
        $this->component->autoUpdate = true;
        $this->component->sourceValue = 'Héllo Wörld & Café';

        $this->component->generateSlug();

        self::assertNotEmpty($this->component->value);
        self::assertStringNotContainsString(' ', $this->component->value);
    }

    public function testDefaultPropertyValues(): void
    {
        self::assertNull($this->component->value);
        self::assertSame('', $this->component->sourceValue);
        self::assertTrue($this->component->isLocked);
        self::assertFalse($this->component->unique);
        self::assertNull($this->component->entityClass);
        self::assertSame('slug', $this->component->slugField);
        self::assertNull($this->component->entityId);
        self::assertSame('', $this->component->name);
        self::assertNull($this->component->repositoryMethod);
        self::assertTrue($this->component->autoUpdate);
        self::assertNull($this->component->targetId);
    }

    public function testEnsureUniquenessWithoutDoctrineReturnsSameSlug(): void
    {
        // Component created without doctrine in setUp
        $this->component->mount(null);
        $this->component->unique = true;
        $this->component->entityClass = 'App\\Entity\\Post';
        $this->component->isLocked = true;
        $this->component->autoUpdate = true;
        $this->component->sourceValue = 'Hello World';

        $this->component->generateSlug();

        self::assertSame('hello-world', $this->component->value);
    }
}
