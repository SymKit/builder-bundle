<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\HowToSchema;
use Symkit\MetadataBundle\JsonLd\Schema\HowToStep;

final class HowToSchemaTest extends TestCase
{
    public function testMinimalHowTo(): void
    {
        $schema = new HowToSchema(
            name: 'Change a tire',
            steps: [
                new HowToStep('Step 1', 'Loosen the lug nuts'),
            ],
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('HowTo', $array['@type']);
        self::assertSame('Change a tire', $array['name']);
        self::assertCount(1, $array['step']);
        self::assertSame('Step 1', $array['step'][0]['name']);
        self::assertSame('Loosen the lug nuts', $array['step'][0]['text']);
    }

    public function testFullHowTo(): void
    {
        $schema = new HowToSchema(
            name: 'Make coffee',
            steps: [
                new HowToStep('Boil', 'Boil water', '/boil.jpg', 'https://example.com/step1'),
                new HowToStep('Pour', 'Pour over grounds'),
            ],
            description: 'How to make great coffee',
            totalTime: 'PT10M',
            image: '/coffee.jpg',
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Make coffee', $array['name']);
        self::assertSame('How to make great coffee', $array['description']);
        self::assertSame('PT10M', $array['totalTime']);
        self::assertSame('/coffee.jpg', $array['image']);
        self::assertCount(2, $array['step']);
        self::assertSame('/boil.jpg', $array['step'][0]['image']);
        self::assertSame('https://example.com/step1', $array['step'][0]['url']);
    }

    public function testStepsAreStoredAsReadonlyList(): void
    {
        $steps = [new HowToStep('S1', 'Text 1'), new HowToStep('S2', 'Text 2')];
        $schema = new HowToSchema(name: 'Test', steps: $steps);

        self::assertCount(2, $schema->steps);
        self::assertSame('S1', $schema->steps[0]->name);
    }
}
