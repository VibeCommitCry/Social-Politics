<?php

namespace App\Import;

use App\Dto\DecisionDto;
use App\Dto\FractionDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use App\Repository\DecisionRepository;
use App\Repository\FractionRepository;



class DecisionImporter implements ImporterInterface
{

    private const DTOSTRING = DecisionDto::class;

    public function __construct(
                private readonly DecisionRepository $decisionRepository,

    ) {
    }


    public function supports(string $dtoClass): bool
    {
        return self::DTOSTRING == $dtoClass;
    }
    public function import(DtoInterface $dto): void
    {
        $this->decisionRepository->saveDtoAsEntity($dto);
    }
}