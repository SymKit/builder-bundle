<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;
use Symkit\MetadataBundle\Renderer\MetaTagRenderer;

final class MetaTagRendererTest extends TestCase
{
    public function testRenderBasic(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Hello World')
            ->setDescription('A test page');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<title>Hello World | Test Site</title>', $html);
        self::assertStringContainsString('<meta name="description" content="A test page">', $html);
        self::assertStringContainsString('<meta property="og:site_name" content="Test Site">', $html);
        self::assertStringContainsString('<meta property="og:type" content="website">', $html);
        self::assertStringContainsString('<meta property="og:title" content="Hello World">', $html);
        self::assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
    }

    public function testRenderWithCanonical(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Page')
            ->setCanonicalUrl('https://example.com/page');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<link rel="canonical" href="https://example.com/page">', $html);
        self::assertStringContainsString('<meta property="og:url" content="https://example.com/page">', $html);
    }

    public function testAutoCanonicalFromRequest(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Auto Canon');

        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://example.com/auto-page'));

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $requestStack);
        $html = $renderer->render();

        self::assertStringContainsString('<link rel="canonical" href="https://example.com/auto-page">', $html);
        self::assertStringContainsString('<meta property="og:url" content="https://example.com/auto-page">', $html);
    }

    public function testExplicitCanonicalOverridesAutoCanonical(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Override')
            ->setCanonicalUrl('https://example.com/explicit');

        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://example.com/auto-page'));

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $requestStack);
        $html = $renderer->render();

        self::assertStringContainsString('<link rel="canonical" href="https://example.com/explicit">', $html);
        self::assertStringNotContainsString('auto-page', $html);
    }

    public function testRenderWithoutCanonicalAndNoRequest(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('No Canon');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringNotContainsString('canonical', $html);
        self::assertStringNotContainsString('og:url', $html);
    }

    public function testRenderWithOgImage(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Image Test')
            ->setOgImage('/custom-image.jpg');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<meta property="og:image" content="/custom-image.jpg">', $html);
        self::assertStringContainsString('<meta name="twitter:image" content="/custom-image.jpg">', $html);
    }

    public function testRenderFallbackOgImage(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Default Image');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo('/default-og.jpg'), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<meta property="og:image" content="/default-og.jpg">', $html);
    }

    public function testRenderWithProperties(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Props')
            ->setProperties([
                'author' => 'John',
                'og:locale' => 'fr_FR',
            ]);

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<meta name="author" content="John">', $html);
        self::assertStringContainsString('<meta property="og:locale" content="fr_FR">', $html);
    }

    public function testRenderFavicons(): void
    {
        $siteInfo = $this->createSiteInfo(
            favicon: '/favicon.ico',
            appleTouchIcon: '/apple-touch.png',
            android192: '/android-192.png',
            android512: '/android-512.png',
        );

        $builder = new PageContextBuilder();
        $builder->setTitle('Icons');

        $renderer = new MetaTagRenderer($builder, $siteInfo, $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<link rel="icon" type="image/x-icon" href="/favicon.ico">', $html);
        self::assertStringContainsString('<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch.png">', $html);
        self::assertStringContainsString('<link rel="icon" type="image/png" sizes="192x192" href="/android-192.png">', $html);
        self::assertStringContainsString('<link rel="icon" type="image/png" sizes="512x512" href="/android-512.png">', $html);
    }

    public function testCustomTitleFormat(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Blog');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack(), '{title} - {siteName}');
        $html = $renderer->render();

        self::assertStringContainsString('<title>Blog - Test Site</title>', $html);
    }

    public function testXssEscaping(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Title with "quotes" & <tags>')
            ->setDescription('Desc <script>alert(1)</script>');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;tags&gt;', $html);
        self::assertStringContainsString('&quot;quotes&quot;', $html);
    }

    public function testRenderWithRobots(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Private Page')
            ->setRobots('noindex, nofollow');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    public function testRenderWithoutRobots(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Public Page');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringNotContainsString('robots', $html);
    }

    public function testRenderWithAuthor(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Article')
            ->setAuthor('Jane Doe');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringContainsString('<meta name="author" content="Jane Doe">', $html);
    }

    public function testRenderWithTwitterSiteAndCreator(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Tweet');

        $renderer = new MetaTagRenderer(
            $builder,
            $this->createSiteInfo(),
            $this->createRequestStack(),
            twitterSite: '@mysite',
            twitterCreator: '@author',
        );
        $html = $renderer->render();

        self::assertStringContainsString('<meta name="twitter:site" content="@mysite">', $html);
        self::assertStringContainsString('<meta name="twitter:creator" content="@author">', $html);
    }

    public function testRenderWithoutTwitterSiteAndCreator(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('No Twitter');

        $renderer = new MetaTagRenderer($builder, $this->createSiteInfo(), $this->createRequestStack());
        $html = $renderer->render();

        self::assertStringNotContainsString('twitter:site', $html);
        self::assertStringNotContainsString('twitter:creator', $html);
    }

    private function createRequestStack(): RequestStack
    {
        return new RequestStack();
    }

    private function createSiteInfo(
        ?string $ogImage = null,
        ?string $favicon = null,
        ?string $appleTouchIcon = null,
        ?string $android192 = null,
        ?string $android512 = null,
    ): SiteInfoProviderInterface {
        $mock = $this->createMock(SiteInfoProviderInterface::class);
        $mock->method('getWebsiteName')->willReturn('Test Site');
        $mock->method('getWebsiteDescription')->willReturn('A test site');
        $mock->method('getDefaultOgImage')->willReturn($ogImage);
        $mock->method('getFavicon')->willReturn($favicon);
        $mock->method('getAppleTouchIcon')->willReturn($appleTouchIcon);
        $mock->method('getAndroidIcon192')->willReturn($android192);
        $mock->method('getAndroidIcon512')->willReturn($android512);

        return $mock;
    }
}
