<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Symkit\MetadataBundle\Contract\SchemaDefinitionInterface;

final readonly class HowToSchema implements SchemaDefinitionInterface
{
    /** @var list<HowToStep> */
    public array $steps;

    /**
     * @param list<HowToStep> $steps
     */
    public function __construct(
        public string $name,
        array $steps,
        public ?string $description = null,
        public ?string $totalTime = null,
        public ?string $image = null,
    ) {
        $this->steps = $steps;
    }

    public function toSchemaOrg(): BaseType
    {
        $howTo = Schema::howTo()->name($this->name);

        if (null !== $this->description) {
            $howTo->description($this->description);
        }

        if (null !== $this->totalTime) {
            $howTo->setProperty('totalTime', $this->totalTime);
        }

        if (null !== $this->image) {
            $howTo->image($this->image);
        }

        $schemaSteps = [];
        foreach ($this->steps as $step) {
            $howToStep = Schema::howToStep()
                ->name($step->name)
                ->text($step->text);

            if (null !== $step->image) {
                $howToStep->image($step->image);
            }

            if (null !== $step->url) {
                $howToStep->url($step->url);
            }

            $schemaSteps[] = $howToStep;
        }

        $howTo->step($schemaSteps);

        return $howTo;
    }
}
