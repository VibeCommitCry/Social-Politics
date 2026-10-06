<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\FractionDto;
use PHPUnit\Framework\TestCase;

final class FractionDtoTest extends TestCase
{
    public function testFullPayloadMapsEveryProperty(): void
    {
        $dto = FractionDto::fromArray([
            'Id' => 'f-1',
            'Nummer' => 7,
            'Afkorting' => 'VVD',
            'NaamNL' => 'Volkspartij voor Vrijheid en Democratie',
            'NaamEN' => "People's Party for Freedom and Democracy",
            'AantalZetels' => 24,
            'AantalStemmen' => 2279130,
            'DatumActief' => '1948-01-01T00:00:00Z',
            'DatumInactief' => '2026-01-01T00:00:00Z',
            'ContentType' => 'image/png',
            'ContentLength' => 1234,
            'GewijzigdOp' => '2026-03-01T10:15:00+02:00',
            'ApiGewijzigdOp' => '2026-03-01T08:15:00Z',
            'Verwijderd' => false,
        ]);

        self::assertSame('f-1', $dto->id);
        self::assertSame(7, $dto->number);
        self::assertSame('VVD', $dto->abbreviation);
        self::assertSame(24, $dto->seatCount);
        self::assertSame(2279130, $dto->voteCount);
        self::assertSame('1948-01-01', $dto->activeFrom?->format('Y-m-d'));
        self::assertSame('2026-01-01', $dto->activeUntil?->format('Y-m-d'));
        self::assertSame('image/png', $dto->contentType);
        self::assertSame(1234, $dto->contentLength);
        self::assertFalse($dto->deleted);
    }

    public function testNullAndMissingFieldsStayNull(): void
    {
        $dto = FractionDto::fromArray(['Id' => 'f-1', 'Afkorting' => null, 'AantalZetels' => null, 'DatumInactief' => null]);

        self::assertNull($dto->abbreviation);
        self::assertNull($dto->seatCount);
        self::assertNull($dto->activeUntil);
        self::assertNull($dto->nameNL);
        self::assertNull($dto->deleted);
    }
}
