<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Service;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaGeneratorInterface;
use Symkit\MetadataBundle\JsonLd\Collector\JsonLdCollector;
use Symkit\MetadataBundle\JsonLd\Schema\FaqItem;
use Symkit\MetadataBundle\JsonLd\Schema\FaqSchema;
use Symkit\MetadataBundle\JsonLd\Service\JsonLdService;

final class JsonLdServiceTest extends TestCase
{
    public function testEmptyResult(): void
    {
        $service = new JsonLdService([], new JsonLdCollector());

        self::assertSame('', $service->generate());
    }

    public function testGenerateFromGenerators(): void
    {
        $generator = $this->createMock(SchemaGeneratorInterface::class);
        $generator->method('generate')
            ->willReturn([Schema::webSite()->name('Test Site')]);

        $service = new JsonLdService([$generator], new JsonLdCollector());
        $json = $service->generate();

        self::assertNotEmpty($json);
        $data = json_decode($json, true);
        self::assertSame('https://schema.org', $data['@context']);
        self::assertCount(1, $data['@graph']);
        self::assertSame('WebSite', $data['@graph'][0]['@type']);
    }

    public function testGenerateFromCollector(): void
    {
        $collector = new JsonLdCollector();
        $collector->add(new FaqSchema([new FaqItem('Q?', 'A.')]));

        $service = new JsonLdService([], $collector);
        $json = $service->generate();

        $data = json_decode($json, true);
        self::assertCount(1, $data['@graph']);
        self::assertSame('FAQPage', $data['@graph'][0]['@type']);
    }

    public function testAggregatesGeneratorsAndCollector(): void
    {
        $generator = $this->createMock(SchemaGeneratorInterface::class);
        $generator->method('generate')
            ->willReturn([Schema::webSite()->name('Site')]);

        $collector = new JsonLdCollector();
        $collector->add(Schema::article()->headline('Article'));

        $service = new JsonLdService([$generator], $collector);
        $json = $service->generate();

        $data = json_decode($json, true);
        self::assertCount(2, $data['@graph']);
    }

    public function testMultipleGenerators(): void
    {
        $gen1 = $this->createMock(SchemaGeneratorInterface::class);
        $gen1->method('generate')->willReturn([Schema::webSite()->name('Site')]);

        $gen2 = $this->createMock(SchemaGeneratorInterface::class);
        $gen2->method('generate')->willReturn([Schema::webPage()->name('Page')]);

        $service = new JsonLdService([$gen1, $gen2], new JsonLdCollector());
        $json = $service->generate();

        $data = json_decode($json, true);
        self::assertCount(2, $data['@graph']);
    }

    public function testContextIsNotDuplicated(): void
    {
        $collector = new JsonLdCollector();
        $collector->add(Schema::article()->headline('Test'));

        $service = new JsonLdService([], $collector);
        $json = $service->generate();

        $data = json_decode($json, true);
        self::assertArrayNotHasKey('@context', $data['@graph'][0]);
    }
}
