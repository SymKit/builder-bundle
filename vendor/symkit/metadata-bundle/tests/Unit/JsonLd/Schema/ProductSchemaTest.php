<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\ProductSchema;

final class ProductSchemaTest extends TestCase
{
    public function testMinimalProduct(): void
    {
        $schema = new ProductSchema(name: 'Widget');
        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Product', $array['@type']);
        self::assertSame('Widget', $array['name']);
        self::assertArrayNotHasKey('offers', $array);
    }

    public function testFullProduct(): void
    {
        $schema = new ProductSchema(
            name: 'Premium Widget',
            price: 29.99,
            currency: 'USD',
            availability: 'https://schema.org/InStock',
            brand: 'Acme',
            sku: 'WDG-001',
            image: '/widget.jpg',
            description: 'A premium widget',
            url: 'https://example.com/widget',
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Premium Widget', $array['name']);
        self::assertSame('A premium widget', $array['description']);
        self::assertSame('/widget.jpg', $array['image']);
        self::assertSame('WDG-001', $array['sku']);
        self::assertSame('https://example.com/widget', $array['url']);
        self::assertSame('Acme', $array['brand']['name']);
        self::assertSame(29.99, $array['offers']['price']);
        self::assertSame('USD', $array['offers']['priceCurrency']);
        self::assertSame('https://schema.org/InStock', $array['offers']['availability']);
    }

    public function testProductWithPriceButNoAvailability(): void
    {
        $schema = new ProductSchema(name: 'Basic', price: 9.99);
        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame(9.99, $array['offers']['price']);
        self::assertSame('EUR', $array['offers']['priceCurrency']);
        self::assertArrayNotHasKey('availability', $array['offers']);
    }
}
