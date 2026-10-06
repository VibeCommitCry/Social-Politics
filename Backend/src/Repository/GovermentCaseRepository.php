<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\GovermentCaseDto;
use App\Entity\GovermentCase;
use App\Interface\DtoInterface;
use App\Interface\ImportDtoInterface;
use App\Interface\SyncableEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GovermentCase>
 */
class GovermentCaseRepository extends ServiceEntityRepository implements ImportDtoInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GovermentCase::class);
    }

    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof GovermentCaseDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', GovermentCase::class, $dto::class));
        }

        $case = $this->find($dto->id) ?? new GovermentCase($dto->id);

        $case->setNumber($dto->number);
        $case->setType($dto->type);
        $case->setTitle($dto->title);
        $case->setSubject($dto->subject);
        $case->setStartedOn($dto->date);
        $case->setOrganization($dto->room);
        $case->setCurrentStatus($dto->currentStatus);
        $case->setCabinetPosition($dto->cabinetAppreciation);
        $case->setCompleted($dto->completed ?? false);
        $case->setDeleted($dto->deleted ?? false);
        $case->setUpdatedAt($dto->modifiedOn);

        $this->getEntityManager()->persist($case);

        $this->flush();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
}
