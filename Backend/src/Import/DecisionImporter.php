<?php

namespace App\Import;

use App\Dto\DecisionDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use App\Repository\DecisionRepository;
use App\Repository\GovermentCaseDecisionRepository;

final class DecisionImporter implements ImporterInterface
{
    public function __construct(
        private readonly DecisionRepository $decisionRepository,
        private readonly GovermentCaseDecisionRepository $caseDecisionRepository,
    ) {
    }

    public function supports(string $dtoClass): bool
    {
        return $dtoClass === DecisionDto::class;
    }

    public function import(DtoInterface $dto): void
    {
        if (!$dto instanceof DecisionDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', DecisionDto::class, $dto::class));
        }
        
        $this->decisionRepository->saveDtoAsEntity($dto);
        $this->caseDecisionRepository->link($dto->caseId, $dto->id);
    }
}