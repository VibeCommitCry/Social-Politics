<?php

namespace App\Import;

use App\Dto\GovermentCaseDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use App\Repository\GovermentCaseRepository;




class GovermentCaseImporter implements ImporterInterface
{
    private const DTOSTRING = GovermentCaseDto::class;
    public function __construct(
        private readonly GovermentCaseRepository $govermentCaseRepository,

    ) {
    }

    public function supports(string $dtoClass): bool
    {
        return self::DTOSTRING == $dtoClass;
    }
    public function import(DtoInterface $dto): void
    {
        $this->govermentCaseRepository->saveDtoAsEntity($dto);
    }

}