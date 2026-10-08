<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncVoteCommand;
use App\Dto\DecisionDto;
use App\Entity\Decision;
use App\Entity\Vote;
use App\Repository\DecisionRepository;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SyncVoteCommandTest extends DatabaseTestCase
{
    private function decisionRepo(): DecisionRepository
    {
        $repo = $this->repository(Decision::class);
        \assert($repo instanceof DecisionRepository);

        return $repo;
    }

    private static function decisionId(int $n): string
    {
        return sprintf('d0000000-0000-4000-8000-%012d', $n);
    }

    private function seedDecisions(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->decisionRepo()->saveDtoAsEntity(new DecisionDto(
                id: self::decisionId($i),
                agendaItemId: self::AGENDA_ITEM_ID,
                // Higher number = more recently modified.
                modifiedOn: new \DateTimeImmutable('2026-01-01 +' . $i . ' minutes'),
            ));
        }
    }

    /**
     * @return list<array<string, string>>
     */
    private static function votes(int $count, string $decisionId, int $offset = 0): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'Id' => sprintf('b0000000-0000-4000-8000-%012d', $offset + $i),
                'Besluit_Id' => $decisionId,
                'Soort' => 'Voor',
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
        return new CommandTester(new SyncVoteCommand(
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

    public function testImportsVotesForSeededDecisions(): void
    {
        $this->seedDecisions(1);

        $tester = $this->command([self::votes(3, self::decisionId(1))]);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(3, $this->em->getRepository(Vote::class)->count([]));
    }

    public function testDecisionIdsAreBatchedIntoChunksOfFifty(): void
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
            self::assertStringContainsString('Besluit_Id in (', $query['$filter']);
            self::assertStringNotContainsString("'", $query['$filter']);
        }
    }

    public function testDecisionLimitNarrowsToTheMostRecentlyModified(): void
    {
        $this->seedDecisions(5);

        $requests = [];
        $tester = $this->command([[]], $requests);
        $tester->execute(['--decision-limit' => 2]);

        self::assertCount(1, $requests);
        self::assertSame(
            sprintf('Verwijderd eq false and Besluit_Id in (%s,%s)', self::decisionId(5), self::decisionId(4)),
            $requests[0]['$filter'],
        );
    }

    public function testMaxStopsBothLoopsWithoutFurtherRequests(): void
    {
        $this->seedDecisions(120);

        $requests = [];
        $tester = $this->command([self::votes(5, self::decisionId(1))], $requests);
        $tester->execute(['--max' => 2, '--top' => 5]);

        self::assertCount(1, $requests, 'No request may follow once --max is hit.');
        self::assertSame(2, $this->em->getRepository(Vote::class)->count([]));
        self::assertStringContainsString('Reached max of 2', $tester->getDisplay());
    }

    // A vote for a Decision that isn't in the DB is deliberately untested: the intended contract is an open decision.
}
