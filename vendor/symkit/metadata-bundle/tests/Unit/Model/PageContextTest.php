<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\Enum\OgType;
use Symkit\MetadataBundle\Enum\TwitterCard;
use Symkit\MetadataBundle\Model\PageContext;

final class PageContextTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $context = new PageContext();

        self::assertSame('', $context->title);
        self::assertSame('', $context->description);
        self::assertNull($context->ogImage);
        self::assertSame(OgType::WEBSITE, $context->ogType);
        self::assertSame(TwitterCard::SUMMARY_LARGE_IMAGE, $context->twitterCard);
        self::assertSame('', $context->canonicalUrl);
        self::assertNull($context->robots);
        self::assertNull($context->author);
        self::assertSame([], $context->properties);
    }

    public function testCustomValues(): void
    {
        $context = new PageContext(
            title: 'My Page',
            description: 'Page description',
            ogImage: '/image.jpg',
            ogType: OgType::ARTICLE,
            twitterCard: TwitterCard::SUMMARY,
            canonicalUrl: 'https://example.com/page',
            robots: 'noindex, nofollow',
            author: 'Jane Doe',
            properties: ['author' => 'John'],
        );

        self::assertSame('My Page', $context->title);
        self::assertSame('Page description', $context->description);
        self::assertSame('/image.jpg', $context->ogImage);
        self::assertSame(OgType::ARTICLE, $context->ogType);
        self::assertSame(TwitterCard::SUMMARY, $context->twitterCard);
        self::assertSame('https://example.com/page', $context->canonicalUrl);
        self::assertSame('noindex, nofollow', $context->robots);
        self::assertSame('Jane Doe', $context->author);
        self::assertSame(['author' => 'John'], $context->properties);
    }
}
