<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\DecisionDto;
use App\Entity\Decision;
use App\Interface\DtoInterface;
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
        $decision = $this->find($dto->id) ?? new Decision($dto->id);

        $decision->setAgendaItemId($dto->agendaItemId);
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

        return array_column($qb->getQuery()->getScalarResult(), 'id');
    }
}
