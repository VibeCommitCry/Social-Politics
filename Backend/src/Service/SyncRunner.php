<?php
namespace App\Service;

use App\Interface\FilterInterface;
use App\Interface\OdataInterface;
use App\Service\ImporterService;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncRunner
{
    public function __construct(
        private OdataInterface $apiProvider,
        private ImporterService $importerService,
    ) {
    }

    public function run(FilterInterface $filter, string $dtoClass, int $max, OutputInterface $output): int
    {
        $importer = $this->importerService->importerForDto($dtoClass);
        $count = 0;

        foreach ($this->apiProvider->search($filter, $dtoClass) as $dto) {
            $importer->import($dto);
            $count++;

            if ($count % 250 === 0) {
                $output->writeln("Synced {$count} so far...");
            }

            if ($count >= $max) {
                $output->writeln("Reached max of {$max}, stopping.");
                break;
            }
        }

        return $count;
    }
}