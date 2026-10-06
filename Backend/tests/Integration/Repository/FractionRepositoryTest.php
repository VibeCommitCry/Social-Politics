<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\FractionDto;
use App\Entity\Fraction;
use App\Repository\FractionRepository;
use App\Tests\Support\DatabaseTestCase;

final class FractionRepositoryTest extends DatabaseTestCase
{
    private function repo(): FractionRepository
    {
        $repo = $this->repository(Fraction::class);
        \assert($repo instanceof FractionRepository);

        return $repo;
    }

    private function save(string $id, ?string $abbreviation = 'VVD', ?int $seats = 10, ?\DateTimeImmutable $until = null, ?bool $deleted = false): void
    {
        $this->repo()->saveDtoAsEntity(new FractionDto(
            id: $id,
            abbreviation: $abbreviation,
            nameNL: $abbreviation === null ? null : 'Naam ' . $abbreviation,
            seatCount: $seats,
            activeUntil: $until,
            deleted: $deleted,
        ));
    }

    public function testNullFieldsStayNullNotFalse(): void
    {
        $this->repo()->saveDtoAsEntity(new FractionDto(id: self::FRACTION_ID));

        $fraction = $this->em->find(Fraction::class, self::FRACTION_ID);

        self::assertNotNull($fraction);
        self::assertNull($fraction->getAbbreviation());
        self::assertNull($fraction->getNameNl());
        self::assertNull($fraction->getNameEn());
        self::assertNull($fraction->getSeatCount());
        self::assertNull($fraction->getVoteCount());
        self::assertNull($fraction->getActiveFrom());
        self::assertNull($fraction->getActiveUntil());
        self::assertNull($fraction->getUpdatedAt());
    }

    public function testNewIdInserts(): void
    {
        $this->save(self::FRACTION_ID, 'VVD', 24);

        $fraction = $this->em->find(Fraction::class, self::FRACTION_ID);

        self::assertNotNull($fraction);
        self::assertSame('VVD', $fraction->getAbbreviation());
        self::assertSame(24, $fraction->getSeatCount());
    }

    public function testExistingIdUpdatesInPlace(): void
    {
        $this->save(self::FRACTION_ID, 'VVD', 24);
        $this->save(self::FRACTION_ID, 'VVD', 22);

        self::assertSame(1, $this->repo()->count([]));
        self::assertSame(22, $this->em->find(Fraction::class, self::FRACTION_ID)?->getSeatCount());
    }

    public function testDeletedDefaultsToFalseWhenDtoHasNull(): void
    {
        $this->save(self::FRACTION_ID, deleted: null);

        self::assertCount(1, $this->repo()->findActive());
    }

    public function testFindActiveIncludesNullAndFutureActiveUntil(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'AAA', 10, null);
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'BBB', 9, new \DateTimeImmutable('+1 year'));

        self::assertSame(['AAA', 'BBB'], array_column($this->repo()->findActive(), 'abbreviation'));
    }

    public function testFindActiveExcludesPastActiveUntil(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'AAA', 10, new \DateTimeImmutable('-1 year'));

        self::assertSame([], $this->repo()->findActive());
    }

    public function testFindActiveExcludesDeletedRegardlessOfActiveUntil(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'AAA', 10, null, true);

        self::assertSame([], $this->repo()->findActive());
    }

    public function testFindActiveExcludesFractionsWithoutSeatCount(): void
    {
        // Current behaviour: a fraction with a null seat count never shows up as "active".
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'AAA', null);

        self::assertSame([], $this->repo()->findActive());
    }

    public function testFindActiveOrdersBySeatCountDescending(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'SMALL', 3);
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'BIG', 30);
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', 'MID', 12);

        self::assertSame(['BIG', 'MID', 'SMALL'], array_column($this->repo()->findActive(), 'abbreviation'));
    }

    // activeUntil == today is deliberately untested: the intended boundary is still an open decision.
}
