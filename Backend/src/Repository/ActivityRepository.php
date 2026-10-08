<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\ActivityDto;
use App\Entity\Activity;
use App\Entity\AgendaPoint;
use App\Interface\DtoInterface;
use App\Interface\ImportDtoInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository implements ImportDtoInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function saveDtoAsEntity(DtoInterface $dto): void
    {
        if (!$dto instanceof ActivityDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', ActivityDto::class, $dto::class));
        }

        if ($dto->id === null) {
            throw new \InvalidArgumentException('Activity has no id');
        }

        $activity = $this->find($dto->id) ?? new Activity($dto->id);

        $activity->setType($dto->type);
        $activity->setNumber($dto->number);
        $activity->setSubject($dto->subject);
        $activity->setDateType($dto->dateType);
        $activity->setDate($dto->date);
        $activity->setStartTime($dto->startTime);
        $activity->setEndTime($dto->endTime);
        $activity->setLocation($dto->location);
        $activity->setPrivate($dto->private);
        $activity->setStatus($dto->status);
        $activity->setParliamentaryYear($dto->parliamentaryYear);
        $activity->setRoom($dto->room);
        $activity->setRemark($dto->remark);
        $activity->setLeadCommitteeName($dto->leadCommitteeName);
        $activity->setLeadCommitteeAbbreviation($dto->leadCommitteeAbbreviation);
        $activity->setLeadCommitteeShortName($dto->leadCommitteeShortName);
        $activity->setLeadCommitteeId($dto->leadCommitteeId);
        $activity->setRequestDate($dto->requestDate);
        $activity->setFirstExtensionRequestDate($dto->firstExtensionRequestDate);
        $activity->setFirstExtensionNoticeDate($dto->firstExtensionNoticeDate);
        $activity->setSecondExtensionRequestDate($dto->secondExtensionRequestDate);
        $activity->setSecondExtensionNoticeDate($dto->secondExtensionNoticeDate);
        $activity->setExpiryDate($dto->expiryDate);
        $activity->setModifiedOn($dto->modifiedOn);
        $activity->setApiModifiedOn($dto->apiModifiedOn);
        $activity->setDeleted($dto->deleted);

        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush();

        $this->linkPendingAgendaPoints($activity);


        $this->getEntityManager()->clear();
    }

    /**
     * Agenda points synced before this activity existed are linked now, so sync order does not matter.
     */
    private function linkPendingAgendaPoints(Activity $activity): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE ' . AgendaPoint::class . ' p SET p.activity = :activity'
                . ' WHERE p.activitySourceId = :id AND p.activity IS NULL'
        )
            ->setParameter('activity', $activity)
            ->setParameter('id', $activity->getId())
            ->execute();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
}
