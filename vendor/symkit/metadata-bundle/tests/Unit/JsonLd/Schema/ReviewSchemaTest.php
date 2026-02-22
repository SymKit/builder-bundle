<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\ReviewSchema;

final class ReviewSchemaTest extends TestCase
{
    public function testMinimalReview(): void
    {
        $schema = new ReviewSchema(author: 'Alice', ratingValue: 4.5);
        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Review', $array['@type']);
        self::assertSame('Alice', $array['author']['name']);
        self::assertSame(4.5, $array['reviewRating']['ratingValue']);
        self::assertSame(5.0, $array['reviewRating']['bestRating']);
        self::assertArrayNotHasKey('itemReviewed', $array);
        self::assertArrayNotHasKey('reviewBody', $array);
    }

    public function testFullReview(): void
    {
        $date = new DateTimeImmutable('2025-02-20');
        $schema = new ReviewSchema(
            author: 'Bob',
            ratingValue: 3.0,
            bestRating: 10,
            itemReviewedName: 'Awesome Product',
            reviewBody: 'It was decent.',
            datePublished: $date,
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Bob', $array['author']['name']);
        self::assertSame(3.0, $array['reviewRating']['ratingValue']);
        self::assertSame(10.0, $array['reviewRating']['bestRating']);
        self::assertSame('Awesome Product', $array['itemReviewed']['name']);
        self::assertSame('It was decent.', $array['reviewBody']);
    }
}
