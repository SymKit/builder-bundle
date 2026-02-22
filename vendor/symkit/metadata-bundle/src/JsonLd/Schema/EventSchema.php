<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use DateTimeInterface;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class EventSchema implements SchemaDefinitionInterface
{
    public function __construct(
        public string $name,
        public DateTimeInterface $startDate,
        public ?DateTimeInterface $endDate = null,
        public ?string $locationName = null,
        public ?string $locationAddress = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?string $performer = null,
        public ?float $offerPrice = null,
        public string $offerCurrency = 'EUR',
        public ?string $offerUrl = null,
    ) {
    }

    public function toSchemaOrg(): BaseType
    {
        $event = Schema::event()
            ->name($this->name)
            ->startDate($this->startDate);

        if (null !== $this->endDate) {
            $event->endDate($this->endDate);
        }

        if (null !== $this->locationName) {
            $place = Schema::place()->name($this->locationName);

            if (null !== $this->locationAddress) {
                $place->address($this->locationAddress);
            }

            $event->location($place);
        }

        if (null !== $this->description) {
            $event->description($this->description);
        }

        if (null !== $this->image) {
            $event->image($this->image);
        }

        if (null !== $this->performer) {
            $event->performer(Schema::person()->name($this->performer));
        }

        if (null !== $this->offerPrice) {
            $offer = Schema::offer()
                ->price($this->offerPrice)
                ->priceCurrency($this->offerCurrency);

            if (null !== $this->offerUrl) {
                $offer->url($this->offerUrl);
            }

            $event->offers($offer);
        }

        return $event;
    }
}
