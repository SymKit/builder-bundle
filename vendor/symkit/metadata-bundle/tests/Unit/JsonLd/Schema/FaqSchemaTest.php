<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\FaqItem;
use Symkit\MetadataBundle\JsonLd\Schema\FaqSchema;

final class FaqSchemaTest extends TestCase
{
    public function testToSchemaOrg(): void
    {
        $schema = new FaqSchema([
            new FaqItem('What is PHP?', 'A programming language.'),
            new FaqItem('What is Symfony?', 'A PHP framework.'),
        ]);

        $result = $schema->toSchemaOrg();
        $array = $result->toArray();

        self::assertSame('FAQPage', $array['@type']);
        self::assertCount(2, $array['mainEntity']);
        self::assertSame('What is PHP?', $array['mainEntity'][0]['name']);
        self::assertSame('A programming language.', $array['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function testStripsTags(): void
    {
        $schema = new FaqSchema([
            new FaqItem('Q?', '<p>Answer with <strong>HTML</strong></p>'),
        ]);

        $result = $schema->toSchemaOrg()->toArray();

        self::assertSame('Answer with HTML', $result['mainEntity'][0]['acceptedAnswer']['text']);
    }
}
