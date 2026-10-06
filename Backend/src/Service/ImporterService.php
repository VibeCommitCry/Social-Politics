<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ImporterService
{
    /** @param iterable<ImporterInterface> $importers */
    public function __construct(
        #[AutowireIterator('app.importer.tag')]
        private iterable $importers,
    ) {}

    public function importerForDto(string $dtoClass): ImporterInterface
    {

        foreach ($this->importers as $importer) {
            if ($importer->supports($dtoClass)) {
                return $importer;
            }
        }

        throw new \LogicException(sprintf('No importer registered for %s', $dtoClass));
    }
}
