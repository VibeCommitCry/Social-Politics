<?php

namespace App\Repository;

use App\Entity\Fraction;
use App\Interface\DtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fraction>
 */
class FractionRepository extends ServiceEntityRepository
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
}
