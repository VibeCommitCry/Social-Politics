<?php

namespace App\Repository;

use App\Dto\VoteDto;
use App\Entity\Decision;
use App\Entity\Fraction;
use App\Entity\Vote;
use App\Interface\DtoInterface;
use App\Interface\ImportDtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository implements ImportDtoInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof VoteDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', VoteDto::class, $dto::class));
        }

        $vote = $this->find($dto->id) ?? new Vote();

        $vote->setId($dto->id);
        $vote->setDecision($this->resolveReference(Decision::class, $dto->decisionId));
        $vote->setFraction($this->resolveReference(Fraction::class, $dto->fractionId));
        $vote->setType($dto->type);
        $vote->setFractionSize($dto->fractionSize);
        $vote->setFractionName($dto->actorFraction);
        $vote->setActorName($dto->actorName);
        $vote->setMistake($dto->mistake);
        $vote->setPersonId($dto->personId);
        $vote->setModifiedOn($dto->modifiedOn);
        $vote->setApiModifiedOn($dto->apiModifiedOn);
        $vote->setDeleted($dto->deleted ?? false);

        $this->getEntityManager()->persist($vote);

        $this->flush();
    }

    private function resolveReference(string $class, ?string $id): ?object
    {
        if ($id === null) {
            return null;
        }

        return $this->getEntityManager()->getReference($class, $id);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
}