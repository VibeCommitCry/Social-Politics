<?php

declare(strict_types=1);

namespace App\Import;

use App\Dto\AgendaPointDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use App\Repository\AgendaPointRepository;

class AgendaPointImporter implements ImporterInterface
{
    private const DTOSTRING = AgendaPointDto::class;

    public function __construct(
        private readonly AgendaPointRepository $agendaPointRepository,
    ) {}


    public function supports(string $dtoClass): bool
    {
        return self::DTOSTRING == $dtoClass;
    }
    public function import(DtoInterface $dto): void
    {
        $this->agendaPointRepository->saveDtoAsEntity($dto);
    }
}
