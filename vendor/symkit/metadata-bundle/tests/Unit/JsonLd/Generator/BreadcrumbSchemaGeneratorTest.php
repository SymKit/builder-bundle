<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Generator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symkit\MetadataBundle\JsonLd\Generator\BreadcrumbSchemaGenerator;
use Symkit\MetadataBundle\Model\BreadcrumbItem;

final class BreadcrumbSchemaGeneratorTest extends TestCase
{
    public function testGenerateWithItems(): void
    {
        $breadcrumbService = $this->createMock(BreadcrumbServiceInterface::class);
        $breadcrumbService->method('getItems')->willReturn([
            new BreadcrumbItem('Home', 'https://example.com/', 1),
            new BreadcrumbItem('Blog', 'https://example.com/blog', 2),
        ]);

        $request = Request::create('https://example.com/blog/post');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $generator = new BreadcrumbSchemaGenerator($requestStack, $breadcrumbService);
        $schemas = iterator_to_array($generator->generate());

        self::assertCount(1, $schemas);

        $array = $schemas[0]->toArray();
        self::assertSame('BreadcrumbList', $array['@type']);
        self::assertCount(2, $array['itemListElement']);
        self::assertSame(1, $array['itemListElement'][0]['position']);
        self::assertSame('Home', $array['itemListElement'][0]['name']);
        self::assertSame('https://example.com/', $array['itemListElement'][0]['item']);
    }

    public function testGenerateWithoutItems(): void
    {
        $breadcrumbService = $this->createMock(BreadcrumbServiceInterface::class);
        $breadcrumbService->method('getItems')->willReturn([]);

        $request = Request::create('https://example.com/');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $generator = new BreadcrumbSchemaGenerator($requestStack, $breadcrumbService);
        $schemas = iterator_to_array($generator->generate());

        self::assertSame([], $schemas);
    }

    public function testGenerateWithoutRequest(): void
    {
        $breadcrumbService = $this->createMock(BreadcrumbServiceInterface::class);
        $requestStack = new RequestStack();

        $generator = new BreadcrumbSchemaGenerator($requestStack, $breadcrumbService);
        $schemas = iterator_to_array($generator->generate());

        self::assertSame([], $schemas);
    }
}
