<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Renderer;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\JsonLd\Collector\JsonLdCollector;
use Symkit\MetadataBundle\JsonLd\Renderer\JsonLdRenderer;
use Symkit\MetadataBundle\JsonLd\Service\JsonLdService;

final class JsonLdRendererTest extends TestCase
{
    public function testRenderEmpty(): void
    {
        $service = new JsonLdService([], new JsonLdCollector());
        $renderer = new JsonLdRenderer($service);

        self::assertSame('', $renderer->render());
    }

    public function testRenderWithSchemas(): void
    {
        $collector = new JsonLdCollector();
        $collector->add(Schema::article()->headline('Test'));

        $service = new JsonLdService([], $collector);
        $renderer = new JsonLdRenderer($service);

        $html = $renderer->render();

        self::assertStringStartsWith('<script type="application/ld+json">', $html);
        self::assertStringEndsWith('</script>', $html);
        self::assertStringContainsString('schema.org', $html);
        self::assertStringContainsString('Test', $html);
    }
}
