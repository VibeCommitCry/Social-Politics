<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\FractionDto;
use App\Entity\Fraction;
use App\Interface\DtoInterface;
use App\Interface\ImportDtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
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
    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof FractionDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', FractionDto::class, $dto::class));
        }

        if ($dto->id === null) {
            throw new \InvalidArgumentException('Fraction has no id');
        }

        $fraction = $this->find($dto->id) ?? new Fraction($dto->id);

        $fraction->setAbbreviation($dto->abbreviation);
        $fraction->setNameEn($dto->nameEN);
        $fraction->setNameNl($dto->nameNL);
        $fraction->setSeatCount($dto->seatCount);
        $fraction->setVoteCount($dto->voteCount);
        $fraction->setActiveFrom($dto->activeFrom);
        $fraction->setActiveUntil($dto->activeUntil);
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

    /**
     * @return list<array{abbreviation: ?string, nameNl: ?string, nameEn: ?string, seatCount: ?int}>
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.deleted = false')
            ->andWhere('f.activeUntil IS NULL OR f.activeUntil >= :now')
            ->andWhere('f.seatCount IS NOT NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('f.seatCount', 'DESC')
            ->select('f.abbreviation', 'f.nameNl', 'f.nameEn', 'f.seatCount')
            ->getQuery()
            ->getResult();
    }
}
