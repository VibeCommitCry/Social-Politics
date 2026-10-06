<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Odata\Filter\VoteFilter;
use App\Dto\VoteDto;
use App\Interface\OdataInterface;
use App\Repository\DecisionRepository;
use App\Service\ImporterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:vote', description: 'Sync Stemming for decisions currently in the database')]
final class SyncVoteCommand extends Command
{
    private const DTOCLASS = VoteDto::class;
    private const BATCH_SIZE = 50;

    public function __construct(
        private OdataInterface $apiProvider,
        private ImporterService $importerService,
        private DecisionRepository $decisionRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('top', null, InputOption::VALUE_REQUIRED, 'Page size', 250)
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Maximum total records to sync in this run', 500)
            ->addOption('decision-limit', null, InputOption::VALUE_REQUIRED, 'Only sync votes for the N most recently modified decisions (default: all)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $max = (int) $input->getOption('max');
        $top = (int) $input->getOption('top');
        $decisionLimitOption = $input->getOption('decision-limit');
        $decisionLimit = $decisionLimitOption !== null ? (int) $decisionLimitOption : null;

        $decisionIds = $this->decisionRepository->findIds($decisionLimit);

        if ($decisionIds === []) {
            $output->writeln('<comment>No decisions found in the database — nothing to sync. Run app:sync:decision first.</comment>');
            return Command::SUCCESS;
        }

        $importer = $this->importerService->importerForDto(self::DTOCLASS);

        try {
            $count = 0;

            foreach (array_chunk($decisionIds, self::BATCH_SIZE) as $batch) {
                $filter = new VoteFilter(
                    top: $top,
                    withSkip: true,
                    decisionIds: $batch,
                );

                foreach ($this->apiProvider->search($filter, self::DTOCLASS) as $dto) {
                    $importer->import($dto);
                    $count++;

                    if ($count % 250 === 0) {
                        $output->writeln("Synced {$count} votes so far...");
                    }

                    if ($count >= $max) {
                        $output->writeln("Reached max of {$max}, stopping.");
                        break 2;
                    }
                }
            }

            $output->writeln("Stemming sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>Stemming sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
