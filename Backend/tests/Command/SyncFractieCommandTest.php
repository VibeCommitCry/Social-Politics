<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncFractieCommand;
use App\Entity\Fraction;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class SyncFractieCommandTest extends DatabaseTestCase
{
    /**
     * @return list<array<string, mixed>>
     */
    private static function fractions(int $count, int $offset = 0): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'Id' => sprintf('f0000000-0000-4000-8000-%012d', $offset + $i),
                'Afkorting' => 'F' . ($offset + $i),
                'NaamNL' => 'Fractie ' . ($offset + $i),
            ];
        }

        return $rows;
    }

    public function testPagesThroughEveryFractionWithStableOrdering(): void
    {
        $requests = [];
        $command = new SyncFractieCommand(
            $this->apiProvider([self::fractions(2), self::fractions(2, 2), self::fractions(1, 4)], $requests),
            $this->importerService(),
        );
        $tester = new CommandTester($command);
        $tester->execute(['--top' => 2]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(5, $this->em->getRepository(Fraction::class)->count([]));
        self::assertSame(['0', '2', '4'], array_column($requests, '$skip'));
        self::assertSame('Id asc', $requests[0]['$orderby']);
    }

    public function testDoesNotFilterToActiveOrNamedFractions(): void
    {
        $requests = [];
        $command = new SyncFractieCommand($this->apiProvider([[]], $requests), $this->importerService());
        new CommandTester($command)->execute([]);

        // Dissolved and nameless fractions are still referenced by old votes, so only `Verwijderd` filters.
        self::assertSame('Verwijderd eq false', $requests[0]['$filter']);
    }

    public function testDissolvedFractionIsImported(): void
    {
        $command = new SyncFractieCommand(
            $this->apiProvider([[[
                'Id' => self::FRACTION_ID,
                'Afkorting' => 'PvdA',
                'DatumInactief' => '2001-01-01T00:00:00Z',
                'AantalZetels' => null,
            ]]]),
            $this->importerService(),
        );
        new CommandTester($command)->execute([]);

        $fraction = $this->em->find(Fraction::class, self::FRACTION_ID);

        self::assertNotNull($fraction);
        self::assertNull($fraction->getSeatCount());
        self::assertNotNull($fraction->getActiveUntil());
    }

    public function testMaxStopsTheRun(): void
    {
        $command = new SyncFractieCommand($this->apiProvider([self::fractions(5)]), $this->importerService());
        $tester = new CommandTester($command);
        $tester->execute(['--max' => 2, '--top' => 5]);

        self::assertSame(2, $this->em->getRepository(Fraction::class)->count([]));
        self::assertStringContainsString('Reached max of 2', $tester->getDisplay());
    }
}
