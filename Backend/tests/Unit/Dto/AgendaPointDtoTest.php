<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\AgendaPointDto;
use PHPUnit\Framework\TestCase;

final class AgendaPointDtoTest extends TestCase
{
    public function testFullPayloadMapsEveryProperty(): void
    {
        $dto = AgendaPointDto::fromArray([
            'Id' => 'a-1',
            'Nummer' => '5',
            'Onderwerp' => 'Stemmingen',
            'Aanvangstijd' => '2026-03-01T10:00:00+01:00',
            'Eindtijd' => '2026-03-01T11:30:00+01:00',
            'Volgorde' => 3,
            'Rubriek' => 'Stemmingen',
            'Notitie' => 'Een notitie',
            'GewijzigdOp' => '2026-03-01T10:15:00+02:00',
            'ApiGewijzigdOp' => '2026-03-01T08:15:00Z',
            'Verwijderd' => false,
            'Activiteit_Id' => 'act-1',
        ]);

        self::assertSame('a-1', $dto->id);
        self::assertSame('5', $dto->number);
        self::assertSame('Stemmingen', $dto->subject);
        self::assertSame('2026-03-01T10:00:00+01:00', $dto->startTime?->format('c'));
        self::assertSame('2026-03-01T11:30:00+01:00', $dto->endTime?->format('c'));
        self::assertSame(3, $dto->sequence);
        self::assertSame('Stemmingen', $dto->heading);
        self::assertSame('Een notitie', $dto->remark);
        self::assertSame('2026-03-01T10:15:00+02:00', $dto->modifiedOn?->format('c'));
        self::assertSame('2026-03-01T08:15:00+00:00', $dto->apiModifiedOn?->format('c'));
        self::assertFalse($dto->deleted);
        self::assertSame('act-1', $dto->activityId);
    }

    public function testMissingKeysBecomeNull(): void
    {
        $dto = AgendaPointDto::fromArray([]);

        self::assertNull($dto->id);
        self::assertNull($dto->startTime);
        self::assertNull($dto->sequence);
        self::assertNull($dto->deleted);
        self::assertNull($dto->activityId);
    }

    public function testEmptyDateStringBecomesNull(): void
    {
        self::assertNull(AgendaPointDto::fromArray(['Aanvangstijd' => ''])->startTime);
    }

    public function testNullActivityIdStaysNull(): void
    {
        self::assertNull(AgendaPointDto::fromArray(['Id' => 'a-1', 'Activiteit_Id' => null])->activityId);
    }
}
