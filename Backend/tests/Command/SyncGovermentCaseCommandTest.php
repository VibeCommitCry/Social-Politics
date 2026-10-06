<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncGovermentCaseCommand;
use App\Entity\Decision;
use App\Entity\GovermentCase;
use App\Entity\GovermentCaseDecision;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class SyncGovermentCaseCommandTest extends DatabaseTestCase
{
    private const AGENDA_ITEM_B = '66666666-6666-4666-8666-666666666666';

    /**
     * @param list<array<string, mixed>> $cases
     * @param list<array<string, string>> $requests
     * @param array<string, mixed>        $input
     */
    private function sync(array $cases, array &$requests = [], array $input = []): CommandTester
    {
        $command = new SyncGovermentCaseCommand($this->apiProvider([$cases], $requests), $this->importerService());
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    public function testEveryDecisionOnACaseIsImportedNotJustTheLast(): void
    {
        $tester = $this->sync([[
            'Id' => self::CASE_ID,
            'Kamer' => 'Tweede Kamer',
            'Besluit' => [
                ['Id' => 'a0000000-0000-4000-8000-000000000001', 'Agendapunt_Id' => self::AGENDA_ITEM_ID],
                ['Id' => 'a0000000-0000-4000-8000-000000000002', 'Agendapunt_Id' => self::AGENDA_ITEM_ID],
                ['Id' => 'a0000000-0000-4000-8000-000000000003', 'Agendapunt_Id' => self::AGENDA_ITEM_B],
            ],
        ]]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(1, $this->em->getRepository(GovermentCase::class)->count([]));
        self::assertSame(3, $this->em->getRepository(Decision::class)->count([]));
        self::assertSame(3, $this->em->getRepository(GovermentCaseDecision::class)->count([]));
    }

    public function testCaseWithoutDecisionsImportsCleanly(): void
    {
        $tester = $this->sync([['Id' => self::CASE_ID, 'Kamer' => 'Tweede Kamer', 'Besluit' => []]]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(1, $this->em->getRepository(GovermentCase::class)->count([]));
        self::assertSame(0, $this->em->getRepository(Decision::class)->count([]));
    }

    public function testRequestsTweedeKamerCasesWithDecisionsExpanded(): void
    {
        $requests = [];
        $this->sync([], $requests);

        self::assertCount(1, $requests);
        self::assertStringContainsString("Kamer eq 'Tweede Kamer'", $requests[0]['$filter']);
        self::assertSame('Besluit', $requests[0]['$expand']);
    }

    public function testStopsAtMax(): void
    {
        $tester = $this->sync([
            ['Id' => 'c0000000-0000-4000-8000-000000000001'],
            ['Id' => 'c0000000-0000-4000-8000-000000000002'],
            ['Id' => 'c0000000-0000-4000-8000-000000000003'],
        ], input: ['--max' => 2]);

        self::assertSame(2, $this->em->getRepository(GovermentCase::class)->count([]));
        self::assertStringContainsString('Reached max of 2', $tester->getDisplay());
    }

    public function testApiFailureReturnsFailure(): void
    {
        $command = new SyncGovermentCaseCommand($this->apiProvider([new \Symfony\Component\HttpClient\Response\MockResponse('', ['http_code' => 400])]), $this->importerService());
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
    }
}
