<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Command;

use Exception;
use Symkit\BuilderBundle\Service\BlockSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'builder:sync-blocks',
    description: 'Synchronize blocks and categories (upsert logic).',
)]
class SyncBlocksCommand extends Command
{
    public function __construct(
        private readonly BlockSynchronizer $blockSynchronizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('snippets', 's', InputOption::VALUE_NONE, 'Include Tailwind snippets synchronization')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $includeSnippets = $input->getOption('snippets');

        $io->title('Synchronizing Blocks');

        try {
            $this->blockSynchronizer->sync($includeSnippets);
            $io->success('Blocks synchronized successfully.');
            if ($includeSnippets) {
                $io->info('Tailwind snippets were included in the synchronization.');
            }
        } catch (Exception $e) {
            $io->error('An error occurred during synchronization: ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
