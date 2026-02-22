<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Generator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;
use Symkit\MetadataBundle\JsonLd\Generator\WebSiteSchemaGenerator;

final class WebSiteSchemaGeneratorTest extends TestCase
{
    public function testGenerateWithRequest(): void
    {
        $generator = $this->createGenerator('https://example.com', '/page');

        $schemas = iterator_to_array($generator->generate());

        self::assertCount(2, $schemas);

        $websiteArray = $schemas[0]->toArray();
        self::assertSame('WebSite', $websiteArray['@type']);
        self::assertSame('https://example.com', $websiteArray['url']);
        self::assertSame('Test Site', $websiteArray['name']);
        self::assertSame('https://example.com/#website', $websiteArray['@id']);

        $orgArray = $schemas[1]->toArray();
        self::assertSame('Organization', $orgArray['@type']);
        self::assertSame('https://example.com/#organization', $orgArray['@id']);
    }

    public function testGenerateWithoutRequest(): void
    {
        $requestStack = new RequestStack();
        $builder = new PageContextBuilder();
        $siteInfo = $this->createSiteInfo();

        $generator = new WebSiteSchemaGenerator($requestStack, $builder, $siteInfo);

        $schemas = iterator_to_array($generator->generate());

        self::assertSame([], $schemas);
    }

    public function testOrganizationIncludesLogo(): void
    {
        $builder = new PageContextBuilder();
        $builder->setOgImage('/logo.jpg');

        $generator = $this->createGeneratorWithBuilder('https://example.com', '/page', $builder);
        $schemas = iterator_to_array($generator->generate());

        $orgArray = $schemas[1]->toArray();
        self::assertArrayHasKey('logo', $orgArray);
        self::assertSame('/logo.jpg', $orgArray['logo']['url']);
    }

    private function createGenerator(string $host, string $path): WebSiteSchemaGenerator
    {
        return $this->createGeneratorWithBuilder($host, $path, new PageContextBuilder());
    }

    private function createGeneratorWithBuilder(string $host, string $path, PageContextBuilder $builder): WebSiteSchemaGenerator
    {
        $request = Request::create($host.$path);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new WebSiteSchemaGenerator($requestStack, $builder, $this->createSiteInfo());
    }

    private function createSiteInfo(?string $ogImage = null): SiteInfoProviderInterface
    {
        $mock = $this->createMock(SiteInfoProviderInterface::class);
        $mock->method('getWebsiteName')->willReturn('Test Site');
        $mock->method('getWebsiteDescription')->willReturn('A description');
        $mock->method('getDefaultOgImage')->willReturn($ogImage);

        return $mock;
    }
}
