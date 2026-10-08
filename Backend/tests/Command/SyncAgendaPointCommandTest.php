<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncAgendaPointCommand;
use App\Dto\DecisionDto;
use App\Entity\AgendaPoint;
use App\Entity\Decision;
use App\Repository\DecisionRepository;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SyncAgendaPointCommandTest extends DatabaseTestCase
{
    private function decisionRepo(): DecisionRepository
    {
        $repo = $this->repository(Decision::class);
        \assert($repo instanceof DecisionRepository);

        return $repo;
    }

    private static function agendaPointId(int $n): string
    {
        return sprintf('a0000000-0000-4000-8000-%012d', $n);
    }

    /**
     * Each decision references its own agenda point, so each seeded decision is one unsynced agenda point.
     */
    private function seedDecisions(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->decisionRepo()->saveDtoAsEntity(new DecisionDto(
                id: sprintf('d0000000-0000-4000-8000-%012d', $i),
                agendaItemId: self::agendaPointId($i),
                // Higher number = more recently modified.
                modifiedOn: new \DateTimeImmutable('2026-01-01 +' . $i . ' minutes'),
            ));
        }
    }

    /**
     * @return list<array<string, string>>
     */
    private static function agendaPoints(int $count, int $offset = 0): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'Id' => self::agendaPointId($offset + $i),
                'Activiteit_Id' => self::ACTIVITY_ID,
                'Onderwerp' => 'Agendapunt ' . ($offset + $i),
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
        return new CommandTester(new SyncAgendaPointCommand(
            $this->apiProvider($responses, $requests),
            $this->importerService(),
            $this->decisionRepo(),
        ));
    }

    public function testWithNoDecisionsItSucceedsWithoutCallingTheApi(): void
    {
        $requests = [];
        $tester = $this->command([], $requests);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame([], $requests);
        self::assertStringContainsString('Run app:sync:decision first', $tester->getDisplay());
    }

    public function testImportsAgendaPointsReferencedByDecisions(): void
    {
        $this->seedDecisions(3);

        $tester = $this->command([self::agendaPoints(3)]);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(3, $this->em->getRepository(AgendaPoint::class)->count([]));
        self::assertStringContainsString('3 total', $tester->getDisplay());
    }

    public function testImportedAgendaPointIsLinkedToItsDecision(): void
    {
        $this->seedDecisions(1);

        $tester = $this->command([self::agendaPoints(1)]);
        $tester->execute([]);

        $decision = $this->em->find(Decision::class, 'd0000000-0000-4000-8000-000000000001');

        self::assertSame(self::agendaPointId(1), $decision?->getAgendaPoint()?->getId());
    }

    public function testAgendaPointWithoutSyncedActivityIsKeptWithNullActivity(): void
    {
        $this->seedDecisions(1);

        $tester = $this->command([self::agendaPoints(1)]);
        $tester->execute([]);

        $agendaPoint = $this->em->find(AgendaPoint::class, self::agendaPointId(1));

        self::assertNotNull($agendaPoint);
        self::assertNull($agendaPoint->getActivity());
        self::assertSame(self::ACTIVITY_ID, $agendaPoint->getActivitySourceId());
    }

    public function testAgendaPointIdsAreBatchedIntoChunksOfFifty(): void
    {
        $this->seedDecisions(120);

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
            self::assertStringNotContainsString("'", substr($query['$filter'], strpos($query['$filter'], 'Id in (')));
        }
    }

    public function testMaxStopsBothLoopsWithoutFurtherRequests(): void
    {
        $this->seedDecisions(120);

        $requests = [];
        $tester = $this->command([self::agendaPoints(5)], $requests);
        $tester->execute(['--max' => 2, '--top' => 5]);

        self::assertCount(1, $requests, 'No request may follow once --max is hit.');
        self::assertSame(2, $this->em->getRepository(AgendaPoint::class)->count([]));
        self::assertStringContainsString('Reached max of 2', $tester->getDisplay());
    }

    public function testApiErrorReturnsFailureAndImportsNothing(): void
    {
        $this->seedDecisions(1);

        $tester = $this->command([new MockResponse('{}', ['http_code' => 500])]);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertSame(0, $this->em->getRepository(AgendaPoint::class)->count([]));
    }
}
