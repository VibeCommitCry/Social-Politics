<?php

declare(strict_types=1);

namespace App\Dto;

use App\Interface\DtoInterface;
use App\Enum\VoteField;
use App\Enum\VoteType;
use DateTimeImmutable;

final class VoteDto implements DtoInterface
{
    use ReadsOdataValues;

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $decisionId = null,
        public readonly ?VoteType $type = null,
        public readonly ?int $fractionSize = null,
        public readonly ?string $actorName = null,
        public readonly ?string $actorFraction = null,
        public readonly ?bool $mistake = null,
        public readonly ?string $personId = null,
        public readonly ?string $fractionId = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::toString($data[VoteField::Id->value] ?? null),
            decisionId: self::toString($data[VoteField::DecisionId->value] ?? null),
            type: self::toVoteType($data[VoteField::Type->value] ?? null),
            fractionSize: self::toInt($data[VoteField::FractionSize->value] ?? null),
            actorName: self::toString($data[VoteField::ActorName->value] ?? null),
            actorFraction: self::toString($data[VoteField::ActorFraction->value] ?? null),
            mistake: self::toBool($data[VoteField::Mistake->value] ?? null),
            personId: self::toString($data[VoteField::PersonId->value] ?? null),
            fractionId: self::toString($data[VoteField::FractionId->value] ?? null),
            modifiedOn: self::toDate($data[VoteField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[VoteField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[VoteField::Deleted->value] ?? null),
        );
    }

    private static function toVoteType(mixed $value): ?VoteType
    {
        $value = self::toString($value);

        return $value === null || $value === '' ? null : VoteType::tryFrom($value);
    }
}
