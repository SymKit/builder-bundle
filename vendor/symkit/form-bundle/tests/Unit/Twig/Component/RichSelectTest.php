<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Twig\Component;

use PHPUnit\Framework\TestCase;
use Symkit\FormBundle\Twig\Component\RichSelect;

final class RichSelectTest extends TestCase
{
    public function testGetFilteredChoicesReturnsAllByDefault(): void
    {
        $component = new RichSelect();
        $component->choices = ['Apple' => 'apple', 'Banana' => 'banana', 'Cherry' => 'cherry'];

        $filtered = $component->getFilteredChoices();

        self::assertCount(3, $filtered);
    }

    public function testGetFilteredChoicesFiltersWhenSearching(): void
    {
        $component = new RichSelect();
        $component->choices = ['Apple' => 'apple', 'Banana' => 'banana', 'Cherry' => 'cherry'];
        $component->searchQuery = 'ban';

        $filtered = $component->getFilteredChoices();

        self::assertCount(1, $filtered);
        self::assertSame('banana', $filtered['Banana']);
    }

    public function testGetFilteredChoicesIsCaseInsensitive(): void
    {
        $component = new RichSelect();
        $component->choices = ['Apple' => 'apple', 'BANANA' => 'banana'];
        $component->searchQuery = 'BAN';

        $filtered = $component->getFilteredChoices();

        self::assertCount(1, $filtered);
    }

    public function testGetSelectedLabelReturnsCorrectLabel(): void
    {
        $component = new RichSelect();
        $component->choices = ['Apple' => 'apple', 'Banana' => 'banana'];
        $component->value = 'banana';

        self::assertSame('Banana', $component->getSelectedLabel());
    }

    public function testGetSelectedLabelReturnsNullForNoSelection(): void
    {
        $component = new RichSelect();
        $component->choices = ['Apple' => 'apple'];
        $component->value = null;

        self::assertNull($component->getSelectedLabel());
    }
}
