<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class LocalBusinessSchema implements SchemaDefinitionInterface
{
    /**
     * @param list<string> $openingHours
     */
    public function __construct(
        public string $name,
        public ?string $streetAddress = null,
        public ?string $addressLocality = null,
        public ?string $postalCode = null,
        public ?string $addressCountry = null,
        public ?string $telephone = null,
        public ?string $email = null,
        public ?string $url = null,
        public ?string $image = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public array $openingHours = [],
    ) {
    }

    public function toSchemaOrg(): BaseType
    {
        $business = Schema::localBusiness()->name($this->name);

        $address = Schema::postalAddress();
        $hasAddress = false;

        if (null !== $this->streetAddress) {
            $address->streetAddress($this->streetAddress);
            $hasAddress = true;
        }

        if (null !== $this->addressLocality) {
            $address->addressLocality($this->addressLocality);
            $hasAddress = true;
        }

        if (null !== $this->postalCode) {
            $address->postalCode($this->postalCode);
            $hasAddress = true;
        }

        if (null !== $this->addressCountry) {
            $address->addressCountry($this->addressCountry);
            $hasAddress = true;
        }

        if ($hasAddress) {
            $business->address($address);
        }

        if (null !== $this->telephone) {
            $business->telephone($this->telephone);
        }

        if (null !== $this->email) {
            $business->email($this->email);
        }

        if (null !== $this->url) {
            $business->url($this->url);
        }

        if (null !== $this->image) {
            $business->image($this->image);
        }

        if (null !== $this->latitude && null !== $this->longitude) {
            $business->geo(
                Schema::geoCoordinates()
                    ->latitude($this->latitude)
                    ->longitude($this->longitude),
            );
        }

        if ([] !== $this->openingHours) {
            $business->openingHours($this->openingHours);
        }

        return $business;
    }
}
