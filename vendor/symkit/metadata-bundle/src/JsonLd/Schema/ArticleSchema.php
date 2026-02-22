<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use DateTimeInterface;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;
use Symkit\MetadataBundle\Enum\ArticleType;

final readonly class ArticleSchema implements SchemaDefinitionInterface
{
    public function __construct(
        public string $headline,
        public ?string $author = null,
        public ?DateTimeInterface $datePublished = null,
        public ?DateTimeInterface $dateModified = null,
        public ?string $image = null,
        public ?string $description = null,
        public ArticleType $type = ArticleType::ARTICLE,
    ) {
    }

    public function toSchemaOrg(): BaseType
    {
        $article = match ($this->type) {
            ArticleType::BLOG_POSTING => Schema::blogPosting(),
            ArticleType::NEWS_ARTICLE => Schema::newsArticle(),
            default => Schema::article(),
        };

        $article->headline($this->headline);

        if (null !== $this->author) {
            $article->author(Schema::person()->name($this->author));
        }

        if (null !== $this->datePublished) {
            $article->datePublished($this->datePublished);
        }

        if (null !== $this->dateModified) {
            $article->dateModified($this->dateModified);
        }

        if (null !== $this->image) {
            $article->image($this->image);
        }

        if (null !== $this->description) {
            $article->description($this->description);
        }

        return $article;
    }
}
