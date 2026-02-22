<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\BuilderBundle\Service\BlockSynchronizer;

final class SyncBlocksCommand extends Command
{
    public function __construct(
        private readonly BlockSynchronizer $blockSynchronizer,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('snippets', 's', InputOption::VALUE_NONE, $this->translator->trans('command.option_snippets', [], 'SymkitBuilderBundle'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $includeSnippets = (bool) $input->getOption('snippets');

        $io->title($this->translator->trans('command.title', [], 'SymkitBuilderBundle'));

        try {
            $this->blockSynchronizer->sync($includeSnippets);
            $io->success($this->translator->trans('command.success', [], 'SymkitBuilderBundle'));
            if ($includeSnippets) {
                $io->info($this->translator->trans('command.snippets_info', [], 'SymkitBuilderBundle'));
            }
        } catch (Exception $e) {
            $io->error($this->translator->trans('command.error', ['%message%' => $e->getMessage()], 'SymkitBuilderBundle'));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
