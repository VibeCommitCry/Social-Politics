<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\ActivityDto;
use App\Dto\AgendaPointDto;
use App\Dto\DecisionDto;
use App\Entity\Activity;
use App\Entity\AgendaPoint;
use App\Repository\ActivityRepository;
use App\Repository\AgendaPointRepository;
use App\Tests\Support\DatabaseTestCase;

final class ActivityRepositoryTest extends DatabaseTestCase
{
    private function repo(): ActivityRepository
    {
        $repo = $this->repository(Activity::class);
        \assert($repo instanceof ActivityRepository);

        return $repo;
    }

    private function save(string $id, ?string $subject = null, ?bool $private = null): void
    {
        $this->repo()->saveDtoAsEntity(new ActivityDto(
            id: $id,
            type: 'Plenair debat',
            subject: $subject,
            private: $private,
        ));
    }

    private function saveAgendaPoint(string $id, ?string $activityId): void
    {
        $agendaPoints = $this->repository(AgendaPoint::class);
        \assert($agendaPoints instanceof AgendaPointRepository);
        $agendaPoints->saveDtoAsEntity(new AgendaPointDto(id: $id, activityId: $activityId));
    }

    public function testNewIdInserts(): void
    {
        $this->save(self::ACTIVITY_ID, 'Debat over zorg');

        $activity = $this->em->find(Activity::class, self::ACTIVITY_ID);

        self::assertNotNull($activity);
        self::assertSame('Debat over zorg', $activity->getSubject());
        self::assertSame('Plenair debat', $activity->getType());
    }

    public function testExistingIdUpdatesInPlace(): void
    {
        $this->save(self::ACTIVITY_ID, 'Eerste');
        $this->save(self::ACTIVITY_ID, 'Tweede');

        self::assertSame(1, $this->repo()->count([]));
        self::assertSame('Tweede', $this->em->find(Activity::class, self::ACTIVITY_ID)?->getSubject());
    }

    public function testMissingIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo()->saveDtoAsEntity(new ActivityDto());
    }

    public function testWrongDtoTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo()->saveDtoAsEntity(new DecisionDto(id: self::DECISION_ID));
    }

    public function testNullableFlagsAreStoredAsGiven(): void
    {
        $this->save(self::ACTIVITY_ID, private: false);

        self::assertFalse($this->em->find(Activity::class, self::ACTIVITY_ID)?->getPrivate());
    }

    public function testSavingLinksAgendaPointsSyncedBeforeTheActivity(): void
    {
        $this->saveAgendaPoint(self::AGENDA_ITEM_ID, self::ACTIVITY_ID);

        self::assertNull($this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID)?->getActivity());

        $this->em->clear();
        $this->save(self::ACTIVITY_ID);

        self::assertSame(
            self::ACTIVITY_ID,
            $this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID)?->getActivity()?->getId(),
        );
    }

    public function testSavingDoesNotLinkAgendaPointsOfOtherActivities(): void
    {
        $this->saveAgendaPoint(self::AGENDA_ITEM_ID, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb1');

        $this->save(self::ACTIVITY_ID);

        self::assertNull($this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID)?->getActivity());
    }
}
