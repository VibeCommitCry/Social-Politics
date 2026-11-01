<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncActivityCommand;
use App\Dto\AgendaPointDto;
use App\Entity\Activity;
use App\Entity\AgendaPoint;
use App\Repository\AgendaPointRepository;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SyncActivityCommandTest extends DatabaseTestCase
{
    private function agendaPointRepo(): AgendaPointRepository
    {
        $repo = $this->repository(AgendaPoint::class);
        \assert($repo instanceof AgendaPointRepository);

        return $repo;
    }

    private static function activityId(int $n): string
    {
        return sprintf('c0000000-0000-4000-8000-%012d', $n);
    }

    /**
     * Each agenda point references its own activity, so each seeded point is one unsynced activity.
     */
    private function seedAgendaPoints(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->agendaPointRepo()->saveDtoAsEntity(new AgendaPointDto(
                id: sprintf('a0000000-0000-4000-8000-%012d', $i),
                activityId: self::activityId($i),
                // Higher number = more recently modified.
                modifiedOn: new \DateTimeImmutable('2026-01-01 +' . $i . ' minutes'),
            ));
        }
    }

    /**
     * @return list<array<string, string>>
     */
    private static function activities(int $count, int $offset = 0): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'Id' => self::activityId($offset + $i),
                'Soort' => 'Plenair debat',
                'Onderwerp' => 'Activiteit ' . ($offset + $i),
            ];
        }

        return $rows;
    }

    /**
     * @param list<MockResponse|list<array<string, mixed>>> $responses
     * @param list<array<string, string>> $requests
     */
    private function command(array $responses, array &$requests = []): CommandTester
    {
        return new CommandTester(new SyncActivityCommand(
            $this->apiProvider($responses, $requests),
            $this->importerService(),
            $this->agendaPointRepo(),
            '2026-01-01',
            14,
        ));
    }

    public function testWithNoAgendaPointsItSucceedsWithoutCallingTheApi(): void
    {
        $requests = [];
        $tester = $this->command([], $requests);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame([], $requests);
        self::assertStringContainsString('Run app:sync:agendapoint first', $tester->getDisplay());
    }

    public function testImportsActivitiesReferencedByAgendaPoints(): void
    {
        $this->seedAgendaPoints(3);

        $tester = $this->command([self::activities(3)]);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(3, $this->em->getRepository(Activity::class)->count([]));
        self::assertStringContainsString('3 total', $tester->getDisplay());
    }

    public function testImportedActivityIsLinkedToItsAgendaPoint(): void
    {
        $this->seedAgendaPoints(1);

        $tester = $this->command([self::activities(1)]);
        $tester->execute([]);

        $agendaPoint = $this->em->find(AgendaPoint::class, 'a0000000-0000-4000-8000-000000000001');

        self::assertSame(self::activityId(1), $agendaPoint?->getActivity()?->getId());
    }

    public function testAlreadySyncedActivitiesAreNotRequestedAgain(): void
    {
        $this->seedAgendaPoints(2);
        $this->command([self::activities(1)])->execute([]);

        $requests = [];
        $tester = $this->command([[]], $requests);
        $tester->execute([]);

        self::assertCount(1, $requests);
        self::assertStringContainsString(self::activityId(2), $requests[0]['$filter']);
        self::assertStringNotContainsString(self::activityId(1), $requests[0]['$filter']);
    }

    public function testActivityIdsAreBatchedIntoChunksOfFifty(): void
    {
        $this->seedAgendaPoints(120);

        $requests = [];
        $tester = $this->command([[], [], []], $requests);
        $tester->execute([]);

        self::assertCount(3, $requests);
        self::assertSame([50, 50, 20], array_map(
            static fn(array $q): int => substr_count($q['$filter'], ',') + 1,
            $requests,
        ));
        foreach ($requests as $query) {
            self::assertStringContainsString('Id in (', $query['$filter']);
            $idsStart = strpos($query['$filter'], 'Id in (');
            self::assertNotFalse($idsStart);
            self::assertStringNotContainsString("'", substr($query['$filter'], $idsStart));
        }
    }

    public function testMaxStopsBothLoopsWithoutFurtherRequests(): void
    {
        $this->seedAgendaPoints(120);

        $requests = [];
        $tester = $this->command([self::activities(5)], $requests);
        $tester->execute(['--max' => 2, '--top' => 5]);

        self::assertCount(1, $requests, 'No request may follow once --max is hit.');
        self::assertSame(2, $this->em->getRepository(Activity::class)->count([]));
        self::assertStringContainsString('Reached max of 2', $tester->getDisplay());
    }

    public function testApiErrorReturnsFailureAndImportsNothing(): void
    {
        $this->seedAgendaPoints(1);

        $tester = $this->command([new MockResponse('{}', ['http_code' => 500])]);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertSame(0, $this->em->getRepository(Activity::class)->count([]));
    }
}
