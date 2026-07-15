<?php

namespace App\Repository;

use App\Entity\SyncCursor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SyncCursorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SyncCursor::class);
    }

    public function save(string $category, string $nextUrl): void
    {
        $cursor = $this->find($category);

        if ($cursor === null) {
            $cursor = new SyncCursor($category, $nextUrl);
            $this->getEntityManager()->persist($cursor);
        } else {
            $cursor->setNextUrl($nextUrl);
        }

        $this->getEntityManager()->flush();
    }
}