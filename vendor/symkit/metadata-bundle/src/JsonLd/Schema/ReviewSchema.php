<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use DateTimeInterface;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class ReviewSchema implements SchemaDefinitionInterface
{
    public function __construct(
        public string $author,
        public float $ratingValue,
        public float $bestRating = 5,
        public ?string $itemReviewedName = null,
        public ?string $reviewBody = null,
        public ?DateTimeInterface $datePublished = null,
    ) {
    }

    public function toSchemaOrg(): BaseType
    {
        $review = Schema::review()
            ->author(Schema::person()->name($this->author))
            ->reviewRating(
                Schema::rating()
                    ->ratingValue($this->ratingValue)
                    ->bestRating($this->bestRating),
            );

        if (null !== $this->itemReviewedName) {
            $review->itemReviewed(Schema::thing()->name($this->itemReviewedName));
        }

        if (null !== $this->reviewBody) {
            $review->reviewBody($this->reviewBody);
        }

        if (null !== $this->datePublished) {
            $review->datePublished($this->datePublished);
        }

        return $review;
    }
}
