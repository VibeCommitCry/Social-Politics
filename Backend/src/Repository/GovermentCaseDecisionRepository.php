<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Decision;
use App\Entity\GovermentCase;
use App\Entity\GovermentCaseDecision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GovermentCaseDecision>
 */
final class GovermentCaseDecisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GovermentCaseDecision::class);
    }

    public function link(string $caseId, string $decisionId): void
    {
        if ($this->find(['govermentCase' => $caseId, 'decision' => $decisionId]) !== null) {
            return;
        }

        $em = $this->getEntityManager();

        $pivot = new GovermentCaseDecision(
            $em->getReference(GovermentCase::class, $caseId) ?? throw new \LogicException('Case reference not found'),
            $em->getReference(Decision::class, $decisionId) ?? throw new \LogicException('Decision reference not found'),
        );

        $em->persist($pivot);
        $this->flush();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
}
