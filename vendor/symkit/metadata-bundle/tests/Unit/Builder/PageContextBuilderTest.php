<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\Enum\OgType;
use Symkit\MetadataBundle\Enum\TwitterCard;

final class PageContextBuilderTest extends TestCase
{
    public function testBuildWithDefaults(): void
    {
        $builder = new PageContextBuilder();
        $context = $builder->build();

        self::assertSame('', $context->title);
        self::assertSame('', $context->description);
        self::assertNull($context->ogImage);
        self::assertSame(OgType::WEBSITE, $context->ogType);
        self::assertSame(TwitterCard::SUMMARY_LARGE_IMAGE, $context->twitterCard);
        self::assertNull($context->robots);
        self::assertNull($context->author);
    }

    public function testBuildWithSetters(): void
    {
        $builder = new PageContextBuilder();
        $builder
            ->setTitle('Test Title')
            ->setDescription('Test Description')
            ->setOgImage('/test.jpg')
            ->setOgType(OgType::ARTICLE)
            ->setTwitterCard(TwitterCard::SUMMARY)
            ->setCanonicalUrl('https://example.com')
            ->setRobots('noindex')
            ->setAuthor('Jane Doe')
            ->setProperties(['key' => 'value']);

        $context = $builder->build();

        self::assertSame('Test Title', $context->title);
        self::assertSame('Test Description', $context->description);
        self::assertSame('/test.jpg', $context->ogImage);
        self::assertSame(OgType::ARTICLE, $context->ogType);
        self::assertSame(TwitterCard::SUMMARY, $context->twitterCard);
        self::assertSame('https://example.com', $context->canonicalUrl);
        self::assertSame('noindex', $context->robots);
        self::assertSame('Jane Doe', $context->author);
        self::assertSame(['key' => 'value'], $context->properties);
    }

    public function testAddPropertiesMerges(): void
    {
        $builder = new PageContextBuilder();
        $builder
            ->setProperties(['a' => 1])
            ->addProperties(['b' => 2]);

        $context = $builder->build();

        self::assertSame(['a' => 1, 'b' => 2], $context->properties);
    }

    public function testGetContextCachesResult(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('Cached');

        $context1 = $builder->getContext();
        $context2 = $builder->getContext();

        self::assertSame($context1, $context2);
    }

    public function testSetterInvalidatesCache(): void
    {
        $builder = new PageContextBuilder();
        $builder->setTitle('First');

        $context1 = $builder->getContext();

        $builder->setTitle('Second');
        $context2 = $builder->getContext();

        self::assertNotSame($context1, $context2);
        self::assertSame('First', $context1->title);
        self::assertSame('Second', $context2->title);
    }

    public function testResetClearsState(): void
    {
        $builder = new PageContextBuilder();
        $builder
            ->setTitle('Before Reset')
            ->setDescription('Description')
            ->setOgImage('/img.jpg')
            ->setOgType(OgType::ARTICLE)
            ->setRobots('noindex')
            ->setAuthor('Author')
            ->setProperties(['key' => 'value']);

        $builder->reset();

        $context = $builder->build();

        self::assertSame('', $context->title);
        self::assertSame('', $context->description);
        self::assertNull($context->ogImage);
        self::assertSame(OgType::WEBSITE, $context->ogType);
        self::assertNull($context->robots);
        self::assertNull($context->author);
        self::assertSame([], $context->properties);
    }
}
