<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncActivityCommand;
use App\Entity\Activity;
use App\Service\SyncRunner;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SyncActivityCommandTest extends DatabaseTestCase
{
    private static function activityId(int $n): string
    {
        return sprintf('c0000000-0000-4000-8000-%012d', $n);
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
            new \DateTimeImmutable('2026-01-01'),
            new SyncRunner($this->apiProvider($responses, $requests), $this->importerService()),
        ));
    }

    public function testImportsActivities(): void
    {
        $tester = $this->command([self::activities(3)]);
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(3, $this->em->getRepository(Activity::class)->count([]));
        self::assertStringContainsString('3 total', $tester->getDisplay());
    }

    public function testFiltersOnInjectedSinceAndTweedeKamer(): void
    {
        $requests = [];
        $this->command([[]], $requests)->execute([]);

        self::assertCount(1, $requests);
        self::assertStringContainsString('Datum ge 2026-01-01', $requests[0]['$filter']);
        self::assertStringContainsString("Kamer eq 'Tweede Kamer'", $requests[0]['$filter']);
    }

    public function testSinceOptionOverridesInjectedDate(): void
    {
        $requests = [];
        $this->command([[]], $requests)->execute(['--since' => '2025-06-01']);

        self::assertStringContainsString('Datum ge 2025-0', $requests[0]['$filter']);
        self::assertStringNotContainsString('2026-01-01', $requests[0]['$filter']);
    }

    public function testMaxStopsWithoutFurtherRequests(): void
    {
        $requests = [];
        $tester = $this->command([self::activities(5)], $requests);
        $tester->execute(['--max' => 2, '--top' => 5]);

        self::assertCount(1, $requests, 'No request may follow once --max is hit.');
        self::assertSame(2, $this->em->getRepository(Activity::class)->count([]));
        self::assertStringContainsString('Reached max of 2', $tester->getDisplay());
    }

    public function testApiErrorReturnsFailureAndImportsNothing(): void
    {
        $tester = $this->command([new MockResponse('{}', ['http_code' => 500])]);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertSame(0, $this->em->getRepository(Activity::class)->count([]));
    }
}
