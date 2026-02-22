<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class ProductSchema implements SchemaDefinitionInterface
{
    public function __construct(
        public string $name,
        public ?float $price = null,
        public string $currency = 'EUR',
        public ?string $availability = null,
        public ?string $brand = null,
        public ?string $sku = null,
        public ?string $image = null,
        public ?string $description = null,
        public ?string $url = null,
    ) {
    }

    public function toSchemaOrg(): BaseType
    {
        $product = Schema::product()->name($this->name);

        if (null !== $this->description) {
            $product->description($this->description);
        }

        if (null !== $this->image) {
            $product->image($this->image);
        }

        if (null !== $this->brand) {
            $product->brand(Schema::brand()->name($this->brand));
        }

        if (null !== $this->sku) {
            $product->sku($this->sku);
        }

        if (null !== $this->url) {
            $product->url($this->url);
        }

        if (null !== $this->price) {
            $offer = Schema::offer()
                ->price($this->price)
                ->priceCurrency($this->currency);

            if (null !== $this->availability) {
                $offer->setProperty('availability', $this->availability);
            }

            $product->offers($offer);
        }

        return $product;
    }
}
