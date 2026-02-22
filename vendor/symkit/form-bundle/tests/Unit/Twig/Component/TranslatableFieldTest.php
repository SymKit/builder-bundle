<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Twig\Component;

use PHPUnit\Framework\TestCase;
use Symkit\FormBundle\Twig\Component\TranslatableField;

final class TranslatableFieldTest extends TestCase
{
    public function testMountInitializesTranslationsForAllLocales(): void
    {
        $component = new TranslatableField();
        $component->mount(['en', 'fr', 'de']);

        self::assertArrayHasKey('en', $component->translations);
        self::assertArrayHasKey('fr', $component->translations);
        self::assertArrayHasKey('de', $component->translations);
    }

    public function testMountSetsActiveLocaleToFirst(): void
    {
        $component = new TranslatableField();
        $component->mount(['en', 'fr']);

        self::assertSame('en', $component->activeLocale);
    }

    public function testMountPreservesExistingTranslations(): void
    {
        $component = new TranslatableField();
        $component->mount(['en', 'fr'], ['en' => 'Hello', 'fr' => 'Bonjour']);

        self::assertSame('Hello', $component->translations['en']);
        self::assertSame('Bonjour', $component->translations['fr']);
    }

    public function testMountFillsMissingLocalesWithEmptyString(): void
    {
        $component = new TranslatableField();
        $component->mount(['en', 'fr', 'de'], ['en' => 'Hello']);

        self::assertSame('Hello', $component->translations['en']);
        self::assertSame('', $component->translations['fr']);
        self::assertSame('', $component->translations['de']);
    }
}
