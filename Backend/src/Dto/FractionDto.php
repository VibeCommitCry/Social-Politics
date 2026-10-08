<?php

declare(strict_types=1);

namespace App\Dto;

use App\Interface\DtoInterface;
use App\Enum\FractionField;
use DateTimeImmutable;

final class FractionDto implements DtoInterface
{
    use ReadsOdataValues;

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?int $number = null,
        public readonly ?string $abbreviation = null,
        public readonly ?string $nameNL = null,
        public readonly ?string $nameEN = null,
        public readonly ?int $seatCount = null,
        public readonly ?int $voteCount = null,
        public readonly ?DateTimeImmutable $activeFrom = null,
        public readonly ?DateTimeImmutable $activeUntil = null,
        public readonly ?string $contentType = null,
        public readonly ?int $contentLength = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::toString($data[FractionField::Id->value] ?? null),
            number: self::toInt($data[FractionField::Number->value] ?? null),
            abbreviation: self::toString($data[FractionField::Abbreviation->value] ?? null),
            nameNL: self::toString($data[FractionField::DutchName->value] ?? null),
            nameEN: self::toString($data[FractionField::EnglishName->value] ?? null),
            seatCount: self::toInt($data[FractionField::SeatCount->value] ?? null),
            voteCount: self::toInt($data[FractionField::VoteCount->value] ?? null),
            activeFrom: self::toDate($data[FractionField::ActiveFrom->value] ?? null),
            activeUntil: self::toDate($data[FractionField::ActiveUntil->value] ?? null),
            contentType: self::toString($data[FractionField::ContentType->value] ?? null),
            contentLength: self::toInt($data[FractionField::ContentLength->value] ?? null),
            modifiedOn: self::toDate($data[FractionField::ModifiedAt->value] ?? null),
            apiModifiedOn: self::toDate($data[FractionField::ApiModifiedAt->value] ?? null),
            deleted: self::toBool($data[FractionField::Deleted->value] ?? null),
        );
    }
}
