<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\ActivityDto;
use PHPUnit\Framework\TestCase;

final class ActivityDtoTest extends TestCase
{
    public function testFullPayloadMapsEveryProperty(): void
    {
        $dto = ActivityDto::fromArray([
            'Id' => 'act-1',
            'Soort' => 'Plenair debat',
            'Nummer' => '2026A00123',
            'Onderwerp' => 'Debat over zorg',
            'DatumSoort' => 'Dag',
            'Datum' => '2026-03-01T00:00:00+01:00',
            'Aanvangstijd' => '2026-03-01T10:00:00+01:00',
            'Eindtijd' => '2026-03-01T12:00:00+01:00',
            'Locatie' => 'Plenaire zaal',
            'Besloten' => false,
            'Status' => 'Gepland',
            'Vergaderjaar' => '2025-2026',
            'Kamer' => 'Tweede Kamer',
            'Notitie' => 'Een notitie',
            'VoortouwCommissieNaam' => 'Commissie voor Zorg',
            'VoortouwCommissieAfkorting' => 'VWS',
            'VoortouwCommissieKorteNaam' => 'Zorg',
            'VoortouwCommissie_Id' => 'com-1',
            'Aanvraagdatum' => '2026-01-01T00:00:00Z',
            'DatumVerzoekEersteVerlenging' => '2026-01-02T00:00:00Z',
            'DatumMededelingEersteVerlenging' => '2026-01-03T00:00:00Z',
            'DatumVerzoekTweedeVerlenging' => '2026-01-04T00:00:00Z',
            'DatumMededelingTweedeVerlenging' => '2026-01-05T00:00:00Z',
            'Vervaldatum' => '2026-01-06T00:00:00Z',
            'GewijzigdOp' => '2026-03-01T10:15:00+02:00',
            'ApiGewijzigdOp' => '2026-03-01T08:15:00Z',
            'Verwijderd' => false,
        ]);

        self::assertSame('act-1', $dto->id);
        self::assertSame('Plenair debat', $dto->type);
        self::assertSame('2026A00123', $dto->number);
        self::assertSame('Debat over zorg', $dto->subject);
        self::assertSame('Dag', $dto->dateType);
        self::assertSame('2026-03-01T00:00:00+01:00', $dto->date?->format('c'));
        self::assertSame('2026-03-01T10:00:00+01:00', $dto->startTime?->format('c'));
        self::assertSame('2026-03-01T12:00:00+01:00', $dto->endTime?->format('c'));
        self::assertSame('Plenaire zaal', $dto->location);
        self::assertFalse($dto->private);
        self::assertSame('Gepland', $dto->status);
        self::assertSame('2025-2026', $dto->parliamentaryYear);
        self::assertSame('Tweede Kamer', $dto->room);
        self::assertSame('Een notitie', $dto->remark);
        self::assertSame('Commissie voor Zorg', $dto->leadCommitteeName);
        self::assertSame('VWS', $dto->leadCommitteeAbbreviation);
        self::assertSame('Zorg', $dto->leadCommitteeShortName);
        self::assertSame('com-1', $dto->leadCommitteeId);
        self::assertSame('2026-01-01', $dto->requestDate?->format('Y-m-d'));
        self::assertSame('2026-01-02', $dto->firstExtensionRequestDate?->format('Y-m-d'));
        self::assertSame('2026-01-03', $dto->firstExtensionNoticeDate?->format('Y-m-d'));
        self::assertSame('2026-01-04', $dto->secondExtensionRequestDate?->format('Y-m-d'));
        self::assertSame('2026-01-05', $dto->secondExtensionNoticeDate?->format('Y-m-d'));
        self::assertSame('2026-01-06', $dto->expiryDate?->format('Y-m-d'));
        self::assertSame('2026-03-01T10:15:00+02:00', $dto->modifiedOn?->format('c'));
        self::assertSame('2026-03-01T08:15:00+00:00', $dto->apiModifiedOn?->format('c'));
        self::assertFalse($dto->deleted);
    }

    public function testMissingKeysBecomeNull(): void
    {
        $dto = ActivityDto::fromArray([]);

        self::assertNull($dto->id);
        self::assertNull($dto->date);
        self::assertNull($dto->private);
        self::assertNull($dto->expiryDate);
        self::assertNull($dto->deleted);
    }

    public function testDateIsConvertedToAmsterdamTime(): void
    {
        $dto = ActivityDto::fromArray(['Datum' => '2026-03-01T23:30:00Z']);

        self::assertSame('Europe/Amsterdam', $dto->date?->getTimezone()->getName());
        self::assertSame('2026-03-02T00:30:00+01:00', $dto->date->format('c'));
    }

    public function testEmptyDateStringBecomesNull(): void
    {
        self::assertNull(ActivityDto::fromArray(['Datum' => ''])->date);
        self::assertNull(ActivityDto::fromArray(['Aanvangstijd' => ''])->startTime);
    }
}
