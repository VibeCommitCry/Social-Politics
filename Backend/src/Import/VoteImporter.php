<?php

declare(strict_types=1);

namespace App\Import;

use App\Dto\FractionDto;
use App\Dto\VoteDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use App\Repository\VoteRepository;

class VoteImporter implements ImporterInterface
{
    private const DTOSTRING = VoteDto::class;

    public function __construct(
        private readonly VoteRepository $voteRepository,
    ) {}


    public function supports(string $dtoClass): bool
    {
        return self::DTOSTRING == $dtoClass;
    }
    public function import(DtoInterface $dto): void
    {
        $this->voteRepository->saveDtoAsEntity($dto);
    }
}
