<?php

namespace App\Command;
use App\Api\Odata\Filter\FractionFilter;
use App\Dto\FractionDto;
use App\Enum\FractionField;
use App\Interface\OdataInterface;
use App\Service\ImporterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:fraction', description: 'Sync all Fractie (including dissolved ones) via OData')]
final class SyncFractieCommand extends Command
{

    private const DTOCLASS = FractionDto::class;
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
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Maximum total records to sync in this run (default: all)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maxOption = $input->getOption('max');
        $max = $maxOption !== null ? (int) $maxOption : PHP_INT_MAX;

        // Old votes reference dissolved and nameless fractions, so only deleted ones are skipped.
        // Ordered by Id so $skip pagination is stable.
        $filter = new FractionFilter(
            top: (int) $input->getOption('top'),
            orderDirection: 'asc',
            orderBy: FractionField::Id,
            includeNamelessFraction: true,
            withSkip: true,
        );
        
        $importer = $this->importerService->importerForDto(self::DTOCLASS);

        try {
            $count = 0;
            foreach ($this->apiProvider->search($filter, self::DTOCLASS) as $dto) {
                $importer->import($dto);
                $count++;

                if ($count % 250 === 0) {
                    $output->writeln("Synced {$count} fractions so far...");
                }

                if ($count >= $max) {
                    $output->writeln("Reached max of {$max}, stopping.");
                    break;
                }
            }

            $output->writeln("Fraction sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>Fraction sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}