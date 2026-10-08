<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\VoteDto;
use App\Enum\VoteType;
use PHPUnit\Framework\TestCase;

final class VoteDtoTest extends TestCase
{
    public function testFullPayloadMapsEveryProperty(): void
    {
        $dto = VoteDto::fromArray([
            'Id' => 'v-1',
            'Besluit_Id' => 'd-1',
            'Soort' => 'Voor',
            'FractieGrootte' => 24,
            'Naam' => 'Jansen',
            'FractieNaam' => 'VVD',
            'Vergissing' => false,
            'Persoon_Id' => 'p-1',
            'Fractie_Id' => 'f-1',
            'GewijzigdOp' => '2026-03-01T10:15:00+02:00',
            'ApiGewijzigdOp' => '2026-03-01T08:15:00Z',
            'Verwijderd' => false,
        ]);

        self::assertSame('v-1', $dto->id);
        self::assertSame('d-1', $dto->decisionId);
        self::assertSame(VoteType::For, $dto->type);
        self::assertSame(24, $dto->fractionSize);
        self::assertSame('Jansen', $dto->actorName);
        self::assertSame('VVD', $dto->actorFraction);
        self::assertFalse($dto->mistake);
        self::assertSame('p-1', $dto->personId);
        self::assertSame('f-1', $dto->fractionId);
        self::assertNotNull($dto->modifiedOn);
        self::assertNotNull($dto->apiModifiedOn);
        self::assertFalse($dto->deleted);
    }

    public function testMissingKeysBecomeNull(): void
    {
        $dto = VoteDto::fromArray([]);

        self::assertNull($dto->id);
        self::assertNull($dto->type);
        self::assertNull($dto->fractionId);
        self::assertNull($dto->mistake);
        self::assertNull($dto->modifiedOn);
    }

    public function testUnknownVoteTypeBecomesNullWithoutThrowing(): void
    {
        self::assertNull(VoteDto::fromArray(['Soort' => 'Onthouden'])->type);
    }

    public function testEmptyDateStringBecomesNull(): void
    {
        self::assertNull(VoteDto::fromArray(['ApiGewijzigdOp' => ''])->apiModifiedOn);
    }
}
