<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\ActivityDto;
use App\Dto\AgendaPointDto;
use App\Dto\DecisionDto;
use App\Entity\Activity;
use App\Entity\AgendaPoint;
use App\Entity\Decision;
use App\Repository\ActivityRepository;
use App\Repository\AgendaPointRepository;
use App\Repository\DecisionRepository;
use App\Tests\Support\DatabaseTestCase;

final class AgendaPointRepositoryTest extends DatabaseTestCase
{
    private function repo(): AgendaPointRepository
    {
        $repo = $this->repository(AgendaPoint::class);
        \assert($repo instanceof AgendaPointRepository);

        return $repo;
    }

    private function save(string $id, ?string $activityId = null, ?string $subject = null, ?string $modifiedOn = null): void
    {
        $this->repo()->saveDtoAsEntity(new AgendaPointDto(
            id: $id,
            subject: $subject,
            modifiedOn: $modifiedOn === null ? null : new \DateTimeImmutable($modifiedOn),
            activityId: $activityId,
        ));
    }

    private function saveActivity(string $id): void
    {
        $activities = $this->repository(\App\Entity\Activity::class);
        \assert($activities instanceof ActivityRepository);
        $activities->saveDtoAsEntity(new ActivityDto(id: $id));
    }

    public function testNewIdInserts(): void
    {
        $this->save(self::AGENDA_ITEM_ID, subject: 'Stemmingen');

        $agendaPoint = $this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID);

        self::assertNotNull($agendaPoint);
        self::assertSame('Stemmingen', $agendaPoint->getSubject());
    }

    public function testExistingIdUpdatesInPlace(): void
    {
        $this->save(self::AGENDA_ITEM_ID, subject: 'Eerste');
        $this->save(self::AGENDA_ITEM_ID, subject: 'Tweede');

        self::assertSame(1, $this->repo()->count([]));
        self::assertSame('Tweede', $this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID)?->getSubject());
    }

    public function testMissingIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo()->saveDtoAsEntity(new AgendaPointDto());
    }

    public function testWrongDtoTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo()->saveDtoAsEntity(new DecisionDto(id: self::DECISION_ID));
    }

    public function testNullActivityIdStoresNullActivity(): void
    {
        $this->save(self::AGENDA_ITEM_ID);

        $agendaPoint = $this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID);

        self::assertNull($agendaPoint?->getActivity());
        self::assertNull($agendaPoint?->getActivitySourceId());
    }

    public function testUnsyncedActivityKeepsSourceIdButNoRelation(): void
    {
        $this->save(self::AGENDA_ITEM_ID, self::ACTIVITY_ID);

        $agendaPoint = $this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID);

        self::assertNotNull($agendaPoint);
        self::assertNull($agendaPoint->getActivity());
        self::assertSame(self::ACTIVITY_ID, $agendaPoint->getActivitySourceId());
    }

    public function testSyncedActivityIsLinked(): void
    {
        $this->saveActivity(self::ACTIVITY_ID);
        $this->save(self::AGENDA_ITEM_ID, self::ACTIVITY_ID);

        $agendaPoint = $this->em->find(AgendaPoint::class, self::AGENDA_ITEM_ID);

        self::assertSame(self::ACTIVITY_ID, $agendaPoint?->getActivity()?->getId());
    }

    public function testSavingLinksDecisionsSyncedBeforeTheAgendaPoint(): void
    {
        $decisions = $this->repository(Decision::class);
        \assert($decisions instanceof DecisionRepository);
        $decisions->saveDtoAsEntity(new DecisionDto(id: self::DECISION_ID, agendaItemId: self::AGENDA_ITEM_ID));

        self::assertNull($this->em->find(Decision::class, self::DECISION_ID)?->getAgendaPoint());

        $this->em->clear();
        $this->save(self::AGENDA_ITEM_ID);

        self::assertSame(
            self::AGENDA_ITEM_ID,
            $this->em->find(Decision::class, self::DECISION_ID)?->getAgendaPoint()?->getId(),
        );
    }

    public function testFindMissingActivityIdsExcludesSyncedAndNullActivities(): void
    {
        $this->saveActivity(self::ACTIVITY_ID);
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', self::ACTIVITY_ID);
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb1');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3');

        self::assertSame(['bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb1'], $this->repo()->findMissingActivityIds());
    }

    private function seedForCheck(string $activityDate, ?string $decisionsSyncedAt): void
    {
        $activity = new Activity(self::ACTIVITY_ID)->setDate(new \DateTimeImmutable($activityDate));
        $agendaPoint = new AgendaPoint(self::AGENDA_ITEM_ID)
            ->setActivity($activity)
            ->setDecisionsSyncedAt($decisionsSyncedAt === null ? null : new \DateTimeImmutable($decisionsSyncedAt));

        $this->em->persist($activity);
        $this->em->persist($agendaPoint);
        $this->em->flush();
    }

    /**
     * @return iterable<string, array{string, ?string, bool}>
     */
    public static function checkCases(): iterable
    {
        yield 'never checked' => ['2026-06-10 10:00', null, true];
        yield 'never checked, outside the window' => ['2026-01-10 10:00', null, true];
        yield 'checked today' => ['2026-06-10 10:00', '2026-06-15 08:00', false];
        yield 'checked over a day ago, inside the window' => ['2026-06-10 10:00', '2026-06-14 08:00', true];
        yield 'checked over a day ago, outside the window' => ['2026-01-10 10:00', '2026-01-11 08:00', false];
        yield 'future activity, never checked' => ['2026-06-20 10:00', null, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('checkCases')]
    public function testFindIdsToCheck(string $activityDate, ?string $decisionsSyncedAt, bool $expected): void
    {
        $this->seedForCheck($activityDate, $decisionsSyncedAt);

        self::assertSame(
            $expected ? [self::AGENDA_ITEM_ID] : [],
            $this->repo()->findIdsToCheck(14, new \DateTimeImmutable('2026-06-15 12:00')),
        );
    }

    public function testFindIdsToCheckSkipsAgendaPointWithoutActivity(): void
    {
        $this->em->persist(new AgendaPoint(self::AGENDA_ITEM_ID));
        $this->em->flush();

        self::assertSame([], $this->repo()->findIdsToCheck(14, new \DateTimeImmutable('2026-06-15 12:00')));
    }

    public function testFindMissingActivityIdsOrdersByMostRecentlyModifiedAndDeduplicates(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb1', modifiedOn: '2026-01-01');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb2', modifiedOn: '2026-03-01');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb2', modifiedOn: '2026-02-01');

        self::assertSame(
            ['bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb2', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb1'],
            $this->repo()->findMissingActivityIds(),
        );
    }
}
