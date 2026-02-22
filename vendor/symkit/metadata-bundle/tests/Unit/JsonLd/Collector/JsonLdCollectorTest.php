<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Collector;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\JsonLd\Collector\JsonLdCollector;
use Symkit\MetadataBundle\JsonLd\Schema\FaqItem;
use Symkit\MetadataBundle\JsonLd\Schema\FaqSchema;

final class JsonLdCollectorTest extends TestCase
{
    public function testAddSchemaDefinition(): void
    {
        $collector = new JsonLdCollector();
        $faq = new FaqSchema([
            new FaqItem('Q1?', 'A1.'),
        ]);

        $result = $collector->add($faq);

        self::assertSame($collector, $result);
        self::assertCount(1, $collector->getSchemas());
    }

    public function testAddSpatieBaseType(): void
    {
        $collector = new JsonLdCollector();
        $recipe = Schema::recipe()->name('Tarte');

        $collector->add($recipe);

        self::assertCount(1, $collector->getSchemas());
        self::assertSame($recipe, $collector->getSchemas()[0]);
    }

    public function testCumulativeSchemas(): void
    {
        $collector = new JsonLdCollector();

        $collector->add(new FaqSchema([new FaqItem('Q?', 'A.')]));
        $collector->add(Schema::article()->headline('Test'));
        $collector->add(Schema::product()->name('Widget'));

        self::assertCount(3, $collector->getSchemas());
    }

    public function testResetClearsSchemas(): void
    {
        $collector = new JsonLdCollector();
        $collector->add(Schema::article()->headline('Test'));

        self::assertCount(1, $collector->getSchemas());

        $collector->reset();

        self::assertCount(0, $collector->getSchemas());
    }

    public function testEmptyByDefault(): void
    {
        $collector = new JsonLdCollector();

        self::assertSame([], $collector->getSchemas());
    }
}
