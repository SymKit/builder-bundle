<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\BuilderBundle\Command\SyncBlocksCommand;
use Symkit\BuilderBundle\Contract\BlockSynchronizerInterface;

final class SyncBlocksCommandTest extends TestCase
{
    public function testExecuteSuccessReturnsZero(): void
    {
        /** @var BlockSynchronizerInterface&\PHPUnit\Framework\MockObject\MockObject $synchronizer */
        $synchronizer = $this->createMock(BlockSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('sync')->with(false);

        /** @var TranslatorInterface&\PHPUnit\Framework\MockObject\MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id) => $id);

        $command = new SyncBlocksCommand($synchronizer, $translator);
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        self::assertSame(0, $command->run($input, $output));
    }

    public function testExecuteWithSnippetsOptionCallsSyncWithTrue(): void
    {
        /** @var BlockSynchronizerInterface&\PHPUnit\Framework\MockObject\MockObject $synchronizer */
        $synchronizer = $this->createMock(BlockSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('sync')->with(true);

        /** @var TranslatorInterface&\PHPUnit\Framework\MockObject\MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id) => $id);

        $command = new SyncBlocksCommand($synchronizer, $translator);
        $input = new ArrayInput(['--snippets' => true]);
        $output = new BufferedOutput();

        $command->run($input, $output);
    }

    public function testExecuteFailureReturnsFailureCode(): void
    {
        /** @var BlockSynchronizerInterface&\PHPUnit\Framework\MockObject\MockObject $synchronizer */
        $synchronizer = $this->createMock(BlockSynchronizerInterface::class);
        $synchronizer->method('sync')->willThrowException(new RuntimeException('db error'));

        /** @var TranslatorInterface&\PHPUnit\Framework\MockObject\MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            static function (string $id, array $params = []) {
                if ('command.error' === $id && isset($params['%message%'])) {
                    return 'Error: '.$params['%message%'];
                }

                return $id;
            },
        );

        $command = new SyncBlocksCommand($synchronizer, $translator);
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        self::assertSame(1, $command->run($input, $output));
        self::assertStringContainsString('db error', $output->fetch());
    }
}
