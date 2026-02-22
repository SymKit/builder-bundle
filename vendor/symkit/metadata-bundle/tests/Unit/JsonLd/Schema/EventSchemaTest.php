<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\EventSchema;

final class EventSchemaTest extends TestCase
{
    public function testMinimalEvent(): void
    {
        $date = new DateTimeImmutable('2025-06-15');
        $schema = new EventSchema(name: 'Concert', startDate: $date);
        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Event', $array['@type']);
        self::assertSame('Concert', $array['name']);
        self::assertArrayNotHasKey('endDate', $array);
        self::assertArrayNotHasKey('location', $array);
    }

    public function testFullEvent(): void
    {
        $start = new DateTimeImmutable('2025-06-15T20:00:00');
        $end = new DateTimeImmutable('2025-06-15T23:00:00');

        $schema = new EventSchema(
            name: 'Rock Festival',
            startDate: $start,
            endDate: $end,
            locationName: 'Stade de France',
            locationAddress: 'Saint-Denis',
            description: 'An awesome festival',
            image: '/festival.jpg',
            performer: 'The Band',
            offerPrice: 89.50,
            offerCurrency: 'EUR',
            offerUrl: 'https://tickets.example.com',
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Rock Festival', $array['name']);
        self::assertSame('An awesome festival', $array['description']);
        self::assertSame('/festival.jpg', $array['image']);
        self::assertSame('Stade de France', $array['location']['name']);
        self::assertSame('Saint-Denis', $array['location']['address']);
        self::assertSame('The Band', $array['performer']['name']);
        self::assertSame(89.50, $array['offers']['price']);
        self::assertSame('EUR', $array['offers']['priceCurrency']);
        self::assertSame('https://tickets.example.com', $array['offers']['url']);
    }

    public function testEventWithLocationButNoAddress(): void
    {
        $schema = new EventSchema(
            name: 'Meetup',
            startDate: new DateTimeImmutable('2025-03-01'),
            locationName: 'The Office',
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('The Office', $array['location']['name']);
        self::assertArrayNotHasKey('address', $array['location']);
    }
}
