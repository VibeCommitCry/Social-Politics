<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncDecisionCommand;
use App\Entity\Decision;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class SyncDecisionCommandTest extends DatabaseTestCase
{
    /**
     * @return list<array<string, string>>
     */
    private static function decisions(int $count, int $offset = 0): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'Id' => sprintf('d0000000-0000-4000-8000-%012d', $offset + $i),
                'Agendapunt_Id' => self::AGENDA_ITEM_ID,
            ];
        }

        return $rows;
    }

    public function testImportsEveryDecisionAcrossPages(): void
    {
        $requests = [];
        $command = new SyncDecisionCommand(
            $this->apiProvider([self::decisions(2), self::decisions(1, 2)], $requests),
            $this->importerService(),
            '2026-01-01',
            14,
        );
        $tester = new CommandTester($command);
        $tester->execute(['--top' => 2]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(3, $this->em->getRepository(Decision::class)->count([]));
        self::assertCount(2, $requests);
        self::assertSame(['0', '2'], array_column($requests, '$skip'));
        self::assertStringContainsString('3 total', $tester->getDisplay());
    }

    public function testStopsAtMaxEvenMidPage(): void
    {
        $requests = [];
        $command = new SyncDecisionCommand(
            $this->apiProvider([self::decisions(5), self::decisions(5, 5)], $requests),
            $this->importerService(),
            '2026-01-01',
            14,
        );
        $tester = new CommandTester($command);
        $tester->execute(['--top' => 5, '--max' => 3]);

        self::assertSame(3, $this->em->getRepository(Decision::class)->count([]));
        self::assertCount(1, $requests);
        self::assertStringContainsString('Reached max of 3', $tester->getDisplay());
    }

    public function testApiErrorReturnsFailureAndImportsNothing(): void
    {
        $command = new SyncDecisionCommand(
            $this->apiProvider([new \Symfony\Component\HttpClient\Response\MockResponse('{}', ['http_code' => 500])]),
            $this->importerService(),
            '2026-01-01',
            14,
        );
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertSame(0, $this->em->getRepository(Decision::class)->count([]));
    }
}
