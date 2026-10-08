<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\DecisionDto;
use App\Enum\DecisionStatus;
use PHPUnit\Framework\TestCase;

final class DecisionDtoTest extends TestCase
{
    public function testFullPayloadMapsEveryProperty(): void
    {
        $dto = DecisionDto::fromArray([
            'Id' => 'd-1',
            'Agendapunt_Id' => 'a-1',
            'Soort' => 'Stemmen - aangenomen',
            'BesluitTekst' => 'Aangenomen.',
            'Notitie' => 'Een notitie',
            'Status' => 'Besluit',
            'Volgorde' => 3,
            'GewijzigdOp' => '2026-03-01T10:15:00+02:00',
            'ApiGewijzigdOp' => '2026-03-01T08:15:00Z',
            'Verwijderd' => false,
        ]);

        self::assertSame('d-1', $dto->id);
        self::assertSame('a-1', $dto->agendaItemId);
        self::assertSame('Stemmen - aangenomen', $dto->type);
        self::assertSame('Aangenomen.', $dto->text);
        self::assertSame('Een notitie', $dto->remark);
        self::assertSame(DecisionStatus::Decision, $dto->status);
        self::assertSame(3, $dto->sequence);
        self::assertSame('2026-03-01T10:15:00+02:00', $dto->modifiedOn?->format('c'));
        self::assertSame('2026-03-01T08:15:00+00:00', $dto->apiModifiedOn?->format('c'));
        self::assertFalse($dto->deleted);
    }

    public function testMissingKeysBecomeNull(): void
    {
        $dto = DecisionDto::fromArray([]);

        self::assertNull($dto->id);
        self::assertNull($dto->status);
        self::assertNull($dto->modifiedOn);
        self::assertNull($dto->deleted);
    }

    public function testUnknownStatusBecomesNullWithoutThrowing(): void
    {
        self::assertNull(DecisionDto::fromArray(['Status' => 'Iets nieuws'])->status);
    }

    public function testEmptyDateStringBecomesNull(): void
    {
        self::assertNull(DecisionDto::fromArray(['GewijzigdOp' => ''])->modifiedOn);
    }

    public function testCaseIdIsPassedThroughAsString(): void
    {
        $dto = DecisionDto::fromArray(['Id' => 'd-1'], caseId: 'case-123');

        self::assertSame('case-123', $dto->caseId);
    }

    public function testCaseIdDefaultsToNull(): void
    {
        self::assertNull(DecisionDto::fromArray(['Id' => 'd-1'])->caseId);
    }
}
