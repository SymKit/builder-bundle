<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symkit\MetadataBundle\Model\BreadcrumbItem;

final class BreadcrumbItemTest extends TestCase
{
    public function testConstruction(): void
    {
        $item = new BreadcrumbItem('Home', 'https://example.com/', 1);

        self::assertSame('Home', $item->name);
        self::assertSame('https://example.com/', $item->url);
        self::assertSame(1, $item->position);
    }

    public function testImmutability(): void
    {
        $item = new BreadcrumbItem('Blog', '/blog', 2);

        $reflection = new ReflectionClass($item);
        self::assertTrue($reflection->isReadOnly());
    }
}
