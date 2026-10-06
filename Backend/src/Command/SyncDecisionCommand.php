<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Odata\Filter\DecisionFilter;
use App\Dto\DecisionDto;
use App\Interface\OdataInterface;
use App\Service\ImporterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:decision', description: 'Sync besluit from the last year via OData')]
final class SyncDecisionCommand extends Command
{
    private const DTOCLASS = DecisionDto::class;
    public function __construct(
        private OdataInterface $apiProvider,
        private ImporterService $importerService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('top', null, InputOption::VALUE_REQUIRED, 'Page size', 250)
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Maximum total records to sync in this run', 500);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $max = (int) $input->getOption('max');

        $filter = new DecisionFilter(
            top: (int) $input->getOption('top'),
            withSkip: true,
        );

        $importer = $this->importerService->importerForDto(self::DTOCLASS);

        try {
            $count = 0;
            foreach ($this->apiProvider->search($filter, self::DTOCLASS) as $dto) {
                $importer->import($dto);
                $count++;

                if ($count % 250 === 0) {
                    $output->writeln("Synced {$count} cases so far...");
                }

                if ($count >= $max) {
                    $output->writeln("Reached max of {$max}, stopping.");
                    break;
                }
            }

            $output->writeln("besluit sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>besluit sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
