<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\SnippetBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class SnippetBlockStrategyTest extends TestCase
{
    private SnippetBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new SnippetBlockStrategy($registry, $twig);
    }

    public function testSupportsTypesStartingWithTw(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'tw_hero', 'data' => []]));
        self::assertTrue($this->strategy->supports(['type' => 'tw_card_section', 'data' => []]));
        self::assertTrue($this->strategy->supports(['type' => 'tw_', 'data' => []]));
    }

    public function testDoesNotSupportTypesNotStartingWithTw(): void
    {
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'code', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'hero_tw', 'data' => []]));
    }

    public function testDoesNotSupportNonStringType(): void
    {
        self::assertFalse($this->strategy->supports(['type' => 123, 'data' => []]));
        self::assertFalse($this->strategy->supports(['data' => []]));
    }
}
