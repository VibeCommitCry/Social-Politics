<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\DtoInterface;
use App\Interface\FilterInterface;
use App\Interface\OdataInterface;
use App\Service\ImporterService;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncRunner
{
    public function __construct(
        private OdataInterface $apiProvider,
        private ImporterService $importerService,
    ) {}

    /**
     * @param FilterInterface<DtoInterface> $filter
     */
    public function run(FilterInterface $filter, int $max, OutputInterface $output): int
    {
        $dtoClass = $filter->dtoClass();
        $importer = $this->importerService->importerForDto($dtoClass);
        $count = 0;

        foreach ($this->apiProvider->search($filter) as $dto) {
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
