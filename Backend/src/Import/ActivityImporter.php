<?php

declare(strict_types=1);

namespace App\Import;

use App\Dto\ActivityDto;
use App\Interface\DtoInterface;
use App\Interface\ImporterInterface;
use App\Repository\ActivityRepository;

class ActivityImporter implements ImporterInterface
{
    private const DTOSTRING = ActivityDto::class;

    public function __construct(
        private readonly ActivityRepository $activityRepository,
    ) {}


    public function supports(string $dtoClass): bool
    {
        return self::DTOSTRING == $dtoClass;
    }
    public function import(DtoInterface $dto): void
    {
        $this->activityRepository->saveDtoAsEntity($dto);
    }
}
