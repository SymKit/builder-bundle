<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\Enum\ArticleType;
use Symkit\MetadataBundle\JsonLd\Schema\ArticleSchema;

final class ArticleSchemaTest extends TestCase
{
    public function testBasicArticle(): void
    {
        $schema = new ArticleSchema(headline: 'Test Article');

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Article', $array['@type']);
        self::assertSame('Test Article', $array['headline']);
    }

    public function testBlogPosting(): void
    {
        $schema = new ArticleSchema(
            headline: 'Blog Post',
            author: 'John',
            type: ArticleType::BLOG_POSTING,
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('BlogPosting', $array['@type']);
        self::assertSame('John', $array['author']['name']);
    }

    public function testFullArticle(): void
    {
        $date = new DateTimeImmutable('2025-01-15');

        $schema = new ArticleSchema(
            headline: 'Full Article',
            author: 'Jane',
            datePublished: $date,
            dateModified: $date,
            image: '/img.jpg',
            description: 'A description',
            type: ArticleType::NEWS_ARTICLE,
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('NewsArticle', $array['@type']);
        self::assertSame('Full Article', $array['headline']);
        self::assertSame('/img.jpg', $array['image']);
        self::assertSame('A description', $array['description']);
    }
}
