<?php

namespace App\Import;

use App\Dto\FractionDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;



class FractionImporter implements ImporterInterface
{

    private const DTOSTRING = FractionDto::class;

    public function __construct(
    ) {
    }


    public function supports(string $dtoClass): bool
    {
        return self::DTOSTRING == $dtoClass;
    }
    public function import(DtoInterface $dto): void
    {

    }
}