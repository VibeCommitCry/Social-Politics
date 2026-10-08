<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\AgendaPointDto;
use App\Entity\Activity;
use App\Entity\AgendaPoint;
use App\Entity\Decision;
use App\Interface\DtoInterface;
use App\Interface\ImportDtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgendaPoint>
 */
class AgendaPointRepository extends ServiceEntityRepository implements ImportDtoInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaPoint::class);
    }

    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof AgendaPointDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', AgendaPointDto::class, $dto::class));
        }

        if ($dto->id === null) {
            throw new \InvalidArgumentException('AgendaPoint has no id');
        }

        $agendaPoint = $this->find($dto->id) ?? new AgendaPoint($dto->id);

        // Unlike other relations this uses find(): an agenda point whose activity is not synced
        // (e.g. outside the Tweede Kamer) is still kept, with a null activity.
        $agendaPoint->setActivitySourceId($dto->activityId);
        $agendaPoint->setActivity(
            $dto->activityId === null
                ? null
                : $this->getEntityManager()->find(Activity::class, $dto->activityId)
        );
        $agendaPoint->setNumber($dto->number);
        $agendaPoint->setSubject($dto->subject);
        $agendaPoint->setStartTime($dto->startTime);
        $agendaPoint->setEndTime($dto->endTime);
        $agendaPoint->setSequence($dto->sequence);
        $agendaPoint->setHeading($dto->heading);
        $agendaPoint->setRemark($dto->remark);
        $agendaPoint->setModifiedOn($dto->modifiedOn);
        $agendaPoint->setApiModifiedOn($dto->apiModifiedOn);
        $agendaPoint->setDeleted($dto->deleted);

        $this->getEntityManager()->persist($agendaPoint);
        $this->getEntityManager()->flush();

        $this->linkPendingDecisions($agendaPoint);

        $this->getEntityManager()->clear();
    }

    /**
     * Decisions synced before this agenda point existed are linked now, so sync order does not matter.
     */
    private function linkPendingDecisions(AgendaPoint $agendaPoint): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE ' . Decision::class . ' d SET d.agendaPoint = :agendaPoint'
                . ' WHERE d.agendaItemId = :id AND d.agendaPoint IS NULL'
        )
            ->setParameter('agendaPoint', $agendaPoint->getId())
            ->setParameter('id', $agendaPoint->getId())
            ->execute();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    /**
     * Activity ids referenced by synced agenda points that are not in the activity table yet.
     *
     * @return list<string>
     */
    public function findMissingActivityIds(): array
    {
        /** @var list<array{activitySourceId: string}> $rows */
        $rows = $this->getEntityManager()->createQuery(
            'SELECT a.activitySourceId FROM ' . AgendaPoint::class . ' a'
                . ' WHERE a.activitySourceId IS NOT NULL'
                . ' AND NOT EXISTS (SELECT 1 FROM ' . Activity::class . ' x WHERE x.id = a.activitySourceId)'
                . ' GROUP BY a.activitySourceId ORDER BY MAX(a.modifiedOn) DESC'
        )->getScalarResult();

        return array_column($rows, 'activitySourceId');
    }
}
