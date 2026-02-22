<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\LocalBusinessSchema;

final class LocalBusinessSchemaTest extends TestCase
{
    public function testMinimalBusiness(): void
    {
        $schema = new LocalBusinessSchema(name: 'My Shop');
        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('LocalBusiness', $array['@type']);
        self::assertSame('My Shop', $array['name']);
        self::assertArrayNotHasKey('address', $array);
        self::assertArrayNotHasKey('geo', $array);
    }

    public function testFullBusiness(): void
    {
        $schema = new LocalBusinessSchema(
            name: 'Bakery',
            streetAddress: '123 Main St',
            addressLocality: 'Paris',
            postalCode: '75001',
            addressCountry: 'FR',
            telephone: '+33-1-23-45-67-89',
            email: 'contact@bakery.com',
            url: 'https://bakery.com',
            image: '/bakery.jpg',
            latitude: 48.8566,
            longitude: 2.3522,
            openingHours: ['Mo-Fr 08:00-18:00', 'Sa 09:00-14:00'],
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Bakery', $array['name']);
        self::assertSame('123 Main St', $array['address']['streetAddress']);
        self::assertSame('Paris', $array['address']['addressLocality']);
        self::assertSame('75001', $array['address']['postalCode']);
        self::assertSame('FR', $array['address']['addressCountry']);
        self::assertSame('+33-1-23-45-67-89', $array['telephone']);
        self::assertSame('contact@bakery.com', $array['email']);
        self::assertSame('https://bakery.com', $array['url']);
        self::assertSame('/bakery.jpg', $array['image']);
        self::assertSame(48.8566, $array['geo']['latitude']);
        self::assertSame(2.3522, $array['geo']['longitude']);
        self::assertSame(['Mo-Fr 08:00-18:00', 'Sa 09:00-14:00'], $array['openingHours']);
    }

    public function testPartialAddress(): void
    {
        $schema = new LocalBusinessSchema(
            name: 'Shop',
            addressLocality: 'Lyon',
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertArrayHasKey('address', $array);
        self::assertSame('Lyon', $array['address']['addressLocality']);
    }

    public function testGeoRequiresBothCoordinates(): void
    {
        $schema = new LocalBusinessSchema(name: 'Shop', latitude: 48.0);
        $array = $schema->toSchemaOrg()->toArray();

        self::assertArrayNotHasKey('geo', $array);
    }
}
