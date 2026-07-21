<?php

namespace App\Repository;

use App\Dto\CaseDto;
use App\Entity\GovermentCase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GovermentCase>
 */
class GovermentCaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GovermentCase::class);
    }

    /**
     * Upserts a GovermentCase (Zaak) from extracted API data.
     */
    public function createGovermentCaseFromArray(array $data): GovermentCase
    {
        $case = $this->find($data['id']) ?? new GovermentCase($data['id']);

        $case->setNumber($data['number']);
        $case->setType($data['type']);
        $case->setTitle($data['title']);
        $case->setSubject($data['subject']);
        $case->setStartedOn($data['startedOn'] ? new \DateTimeImmutable($data['startedOn']) : null);
        $case->setOrganization($data['organization']);
        $case->setCurrentStatus($data['currentStatus']);
        $case->setCabinetPosition($data['cabinetPosition']);
        $case->setCompleted($data['completed']);
        $case->setDeleted($data['deleted']);
        $case->setUpdatedAt(new \DateTimeImmutable());

        $this->getEntityManager()->persist($case);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        return $case;
    }

    public function upsertFromDto(CaseDto $dto): GovermentCase
    {
        $case = $this->find($dto->id) ?? new GovermentCase($dto->id);

        $case->setNumber($dto->number);
        $case->setType($dto->type);
        $case->setTitle($dto->title);
        $case->setSubject($dto->subject);
        $case->setStartedOn($dto->date);
        $case->setOrganization($dto->room);
        $case->setCurrentStatus($dto->currentStatus); // NEW — was missing, column was always empty
        $case->setCabinetPosition($dto->cabinetAppreciation);
        $case->setCompleted($dto->completed ?? false);
        $case->setDeleted($dto->deleted ?? false);
        $case->setUpdatedAt($dto->modifiedOn);

        $this->getEntityManager()->persist($case);

        return $case;
    }
}
