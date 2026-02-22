<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use DateTimeInterface;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class VideoSchema implements SchemaDefinitionInterface
{
    public function __construct(
        public string $name,
        public string $description,
        public string $thumbnailUrl,
        public DateTimeInterface $uploadDate,
        public ?string $duration = null,
        public ?string $contentUrl = null,
        public ?string $embedUrl = null,
    ) {
    }

    public function toSchemaOrg(): BaseType
    {
        $video = Schema::videoObject()
            ->name($this->name)
            ->description($this->description)
            ->thumbnailUrl($this->thumbnailUrl)
            ->uploadDate($this->uploadDate);

        if (null !== $this->duration) {
            $video->setProperty('duration', $this->duration);
        }

        if (null !== $this->contentUrl) {
            $video->contentUrl($this->contentUrl);
        }

        if (null !== $this->embedUrl) {
            $video->embedUrl($this->embedUrl);
        }

        return $video;
    }
}
