<?php

namespace App\Command;
use App\Api\Odata\Filter\CaseFilter;
use App\Dto\CaseDto;
use App\Interface\OdataInterface;
use App\Repository\GovermentCaseRepository;
use App\Service\ImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:case', description: 'Sync Zaak from the last year via OData')]
final class SyncGovermentCaseCommand extends Command
{

    private const DTOCLASS = CaseDto::class;
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

        $filter = new CaseFilter(
            top: (int) $input->getOption('top'),
            room: 'Tweede Kamer',
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

            $output->writeln("Zaak sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>Zaak sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}