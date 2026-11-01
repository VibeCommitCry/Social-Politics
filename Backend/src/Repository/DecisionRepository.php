<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\DecisionDto;
use App\Entity\AgendaPoint;
use App\Entity\Decision;
use App\Interface\DtoInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Decision>
 */
final class DecisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Decision::class);
    }

    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof DecisionDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', DecisionDto::class, $dto::class));
        }
        if ($dto->id === null || $dto->agendaItemId === null) {
            throw new \InvalidArgumentException('Decision has no id or agenda item');
        }

        $decision = $this->find($dto->id) ?? new Decision($dto->id);

        $decision->setAgendaItemId($dto->agendaItemId);
        // find(), not getReference(): an agenda point that is not synced (yet) leaves the relation null.
        $decision->setAgendaPoint($this->getEntityManager()->find(AgendaPoint::class, $dto->agendaItemId));
        $decision->setType($dto->type);
        $decision->setText($dto->text);
        $decision->setRemark($dto->remark);
        $decision->setStatus($dto->status);
        $decision->setSequence($dto->sequence);
        $decision->setModifiedOn($dto->modifiedOn);
        $decision->setApiModifiedOn($dto->apiModifiedOn);
        $decision->setDeleted($dto->deleted ?? false);

        $this->getEntityManager()->persist($decision);

        $this->flush();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    /**
     * Agenda point ids referenced by synced decisions that are not in the agenda point table yet.
     *
     * @return list<string>
     */
    public function findMissingAgendaPointIds(): array
    {
        /** @var list<array{agendaItemId: string}> $rows */
        $rows = $this->getEntityManager()->createQuery(
            'SELECT d.agendaItemId FROM ' . Decision::class . ' d'
            . ' WHERE NOT EXISTS (SELECT 1 FROM ' . AgendaPoint::class . ' a WHERE a.id = d.agendaItemId)'
            . ' GROUP BY d.agendaItemId ORDER BY MAX(d.modifiedOn) DESC'
        )->getScalarResult();

        return array_column($rows, 'agendaItemId');
    }

    /**
     * @return list<string>
     */
    public function findIds(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d.id')
            ->orderBy('d.modifiedOn', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        /** @var list<array{id: string}> $rows */
        $rows = $qb->getQuery()->getScalarResult();

        return array_column($rows, 'id');
    }

    /**
     * @return list<string>
     */
    public function findIdsToCheck(int $recheck, DateTimeImmutable $now = new DateTimeImmutable()): array
    {
        /** @var list<string> */
        return $this->createQueryBuilder('e')
            ->select('e.id')
            ->join('e.agendaPoint', 'ap')
            ->join('ap.activity', 'a')
            ->where('a.date <= :now')
            ->andWhere('e.votesSyncedAt IS NULL OR (e.votesSyncedAt < :dayAgo AND a.date >= :windowStart)')
            ->setParameter('now', $now)
            ->setParameter('dayAgo', $now->modify('-1 day'))
            ->setParameter('windowStart', $now->modify("-{$recheck} days"))
            ->getQuery()
            ->getSingleColumnResult();
    }
}
