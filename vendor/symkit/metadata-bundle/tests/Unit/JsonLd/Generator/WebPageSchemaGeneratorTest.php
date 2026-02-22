<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Generator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\JsonLd\Generator\WebPageSchemaGenerator;

final class WebPageSchemaGeneratorTest extends TestCase
{
    public function testGenerateWithRequest(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('My Page')->setDescription('Page description');

        $request = Request::create('https://example.com/about');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $generator = new WebPageSchemaGenerator($requestStack, $builder);
        $schemas = iterator_to_array($generator->generate());

        self::assertCount(1, $schemas);

        $array = $schemas[0]->toArray();
        self::assertSame('WebPage', $array['@type']);
        self::assertSame('My Page', $array['name']);
        self::assertSame('Page description', $array['description']);
        self::assertStringContainsString('#webpage', $array['@id']);
    }

    public function testGenerateWithoutRequest(): void
    {
        $builder = new PageContextBuilder();
        $requestStack = new RequestStack();

        $generator = new WebPageSchemaGenerator($requestStack, $builder);
        $schemas = iterator_to_array($generator->generate());

        self::assertSame([], $schemas);
    }
}
