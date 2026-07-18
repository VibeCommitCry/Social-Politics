<?php

namespace App\Repository;

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
}
