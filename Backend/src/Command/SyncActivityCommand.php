<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Odata\Filter\ActivityFilter;
use App\Dto\ActivityDto;
use App\Interface\OdataInterface;
use App\Repository\AgendaPointRepository;
use App\Service\ImporterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:activity', description: 'Sync all Tweede Kamer Activiteit via OData')]
final class SyncActivityCommand extends Command
{
    use ReadsIntOptions;

    private const BATCH_SIZE = 50;
    private const DTOCLASS = ActivityDto::class;
    public function __construct(
        private OdataInterface $apiProvider,
        private ImporterService $importerService,
        private AgendaPointRepository $agendaPointRepository,
        private readonly string $syncSince,
        private readonly int $recheck,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('top', null, InputOption::VALUE_REQUIRED, 'Page size', 250)
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Maximum total records to sync in this run', 500)
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'From this to to the present', $this->syncSince);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $max = $this->intOption($input, 'max') ?? 500;

        $top = $this->intOption($input, 'top') ?? 250;
        $ids = $this->agendaPointRepository->findMissingActivityIds();

        if ($ids === []) {
            $output->writeln('<comment>No unsynced activities referenced by agenda points. Run app:sync:agendapoint first.</comment>');
            return Command::SUCCESS;
        }

        $importer = $this->importerService->importerForDto(self::DTOCLASS);

        try {
            $count = 0;
            foreach (array_chunk($ids, self::BATCH_SIZE) as $batch) {
                // Ordered by Id so $skip pagination is stable.
                $filter = new ActivityFilter(top: $top, withSkip: true, room: 'Tweede Kamer', ids: $batch);

                foreach ($this->apiProvider->search($filter, self::DTOCLASS) as $dto) {
                    $importer->import($dto);
                    $count++;

                    if ($count % 250 === 0) {
                        $output->writeln("Synced {$count} activities so far...");
                    }

                    if ($count >= $max) {
                        $output->writeln("Reached max of {$max}, stopping.");
                        break 2;
                    }
                }
            }

            $output->writeln("Activity sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>Activity sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
