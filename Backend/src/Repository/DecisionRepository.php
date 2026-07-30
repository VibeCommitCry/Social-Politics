<?php

namespace App\Repository;

use App\Dto\DecisionDto;
use App\Entity\Decision;
use App\Interface\DtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DecisionRepository extends ServiceEntityRepository
{
    private int $count = 0;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Decision::class);
    }

    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof DecisionDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', DecisionDto::class, $dto::class));
        }

        $decision = $this->find($dto->id) ?? new Decision();

        $decision->setId($dto->id);
        $decision->setAgendaItemId($dto->agendaItemId);
        $decision->setVoteMethod($dto->voteMethod);
        $decision->setType($dto->type);
        $decision->setText($dto->text);
        $decision->setRemark($dto->remark);
        $decision->setStatus($dto->status);
        $decision->setSequence($dto->sequence);
        $decision->setModifiedOn($dto->modifiedOn);
        $decision->setApiModifiedOn($dto->apiModifiedOn);
        $decision->setDeleted($dto->deleted ?? false);

        $this->getEntityManager()->persist($decision);

        if (++$this->count % 100 === 0) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
}