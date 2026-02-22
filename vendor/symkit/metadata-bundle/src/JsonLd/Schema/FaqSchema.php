<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class FaqSchema implements SchemaDefinitionInterface
{
    /** @var list<FaqItem> */
    public array $items;

    /**
     * @param list<FaqItem> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function toSchemaOrg(): BaseType
    {
        $questions = array_map(
            static fn (FaqItem $item): BaseType => Schema::question()
                ->name($item->question)
                ->acceptedAnswer(
                    Schema::answer()->text(strip_tags($item->answer)),
                ),
            $this->items,
        );

        return Schema::fAQPage()->mainEntity($questions);
    }
}
