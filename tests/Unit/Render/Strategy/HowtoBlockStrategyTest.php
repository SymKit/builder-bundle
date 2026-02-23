<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\HowtoBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class HowtoBlockStrategyTest extends TestCase
{
    private HowtoBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new HowtoBlockStrategy($registry, $twig);
    }

    public function testSupportsHowtoType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'howto', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testPrepareDataReturnsUnchanged(): void
    {
        $data = ['steps' => [['title' => 'Step 1', 'content' => 'Do this']]];
        self::assertSame($data, $this->strategy->prepareData($data));
    }
}
