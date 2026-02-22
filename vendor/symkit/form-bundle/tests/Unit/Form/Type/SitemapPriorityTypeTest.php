<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symkit\FormBundle\Form\Type\SitemapPriorityType;

final class SitemapPriorityTypeTest extends TypeTestCase
{
    public function testGetParent(): void
    {
        $type = new SitemapPriorityType();
        $this->assertSame(ChoiceType::class, $type->getParent());
    }

    public function testDefaultOptions(): void
    {
        $form = $this->factory->create(SitemapPriorityType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertSame('form.type.sitemap_priority.label', $options['label']);
        $this->assertFalse($options['searchable']);
        $this->assertSame('form.type.sitemap_priority.placeholder', $options['placeholder']);

        $choices = $options['choices'];
        $this->assertCount(10, $choices);
        $this->assertArrayHasKey('1.0', $choices);
        $this->assertArrayHasKey('0.1', $choices);
        $this->assertSame(1.0, $choices['1.0']);
        $this->assertSame(0.1, $choices['0.1']);
    }

    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(SitemapPriorityType::class);

        $form->submit('0.8');
        $this->assertTrue($form->isSynchronized());
        $this->assertSame(0.8, $form->getData());
    }
}
