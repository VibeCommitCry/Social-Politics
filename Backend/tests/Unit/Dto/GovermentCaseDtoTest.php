<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\DecisionDto;
use App\Dto\GovermentCaseDto;
use PHPUnit\Framework\TestCase;

final class GovermentCaseDtoTest extends TestCase
{
    public function testExpandedDecisionsBecomeDecisionDtos(): void
    {
        $dto = GovermentCaseDto::fromArray([
            'Id' => 'c-1',
            'Kamer' => 'Tweede Kamer',
            'Afgedaan' => true,
            'Datum' => '2026-02-10T00:00:00Z',
            'Besluit' => [
                ['Id' => 'd-1', 'Agendapunt_Id' => 'a-1'],
                ['Id' => 'd-2', 'Agendapunt_Id' => 'a-1'],
                ['Id' => 'd-3', 'Agendapunt_Id' => 'a-2'],
            ],
        ]);

        self::assertSame('c-1', $dto->id);
        self::assertSame('Tweede Kamer', $dto->room);
        self::assertTrue($dto->completed);
        self::assertNotNull($dto->date);
        self::assertContainsOnlyInstancesOf(DecisionDto::class, $dto->decisions);
        self::assertSame(['d-1', 'd-2', 'd-3'], array_map(static fn(DecisionDto $d) => $d->id, $dto->decisions ?? []));
    }

    public function testWithoutExpandDecisionsIsNull(): void
    {
        self::assertNull(GovermentCaseDto::fromArray(['Id' => 'c-1'])->decisions);
    }

    public function testEmptyDecisionListIsEmptyArray(): void
    {
        self::assertSame([], GovermentCaseDto::fromArray(['Id' => 'c-1', 'Besluit' => []])->decisions);
    }
}
