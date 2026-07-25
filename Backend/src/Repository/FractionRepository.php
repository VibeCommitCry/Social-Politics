<?php

namespace App\Repository;

use App\Dto\FractionDto;
use App\Entity\Fraction;
use App\Interface\DtoInterface;
use App\Interface\ImportDtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fraction>
 */
class FractionRepository extends ServiceEntityRepository implements ImportDtoInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fraction::class);
    }

    public function createFractionFromArray(array $data): Fraction
    {
        $fractie = $this->find($data['id']) ?? new Fraction($data['id']);

        $fractie->setAbbreviation($data['abbreviation']);
        $fractie->setNameNl($data['nameNl']);
        $fractie->setNameEN($data['nameEn']);
        $fractie->setSeatCount($data['seatCount']);
        $fractie->setDeleted($data['deleted']);

        $this->getEntityManager()->persist($fractie);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        return $fractie;
    }

      public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof FractionDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', FractionDto::class, $dto::class));
        }

        $fraction = $this->find($dto->id) ?? new Fraction($dto->id);

        $fraction->setAbbreviation($dto->abbreviation ?? false);
        $fraction->setNameEn($dto->nameEN ?? false);
        $fraction->setNameNl($dto->nameNL ?? false);
        $fraction->setSeatCount($dto->seatCount ?? false);
        $fraction->setVoteCount($dto->voteCount ?? false);
        $fraction->setActiveFrom($dto->activeFrom ?? false);
        $fraction->setActiveUntil($dto->activeUntil ?? null);
        $fraction->setDeleted($dto->deleted ?? false);
        $fraction->setUpdatedAt($dto->modifiedOn);

        $this->getEntityManager()->persist($fraction);

        $this->flush();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
}
