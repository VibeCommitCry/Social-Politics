<?php

namespace App\Dto;

use App\Interface\DtoInterface;
use App\Enum\VoteField;
use App\Enum\VoteType;
use DateTimeImmutable;

final class VoteDto implements DtoInterface
{
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
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data[VoteField::Id->value] ?? null,
            decisionId: $data[VoteField::DecisionId->value] ?? null,
            type: self::toVoteType($data[VoteField::Type->value] ?? null),
            fractionSize: $data[VoteField::FractionSize->value] ?? null,
            actorName: $data[VoteField::ActorName->value] ?? null,
            actorFraction: $data[VoteField::ActorFraction->value] ?? null,
            mistake: self::toBool($data[VoteField::Mistake->value] ?? null),
            personId: $data[VoteField::PersonId->value] ?? null,
            fractionId: $data[VoteField::FractionId->value] ?? null,
            modifiedOn: self::toDate($data[VoteField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[VoteField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[VoteField::Deleted->value] ?? null),
        );
    }

    private static function toVoteType(?string $value): ?VoteType
    {
        if ($value === null || $value === '') {
            return null;
        }

        return VoteType::tryFrom($value);
    }

    private static function toDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }

    private static function toBool(?bool $value): ?bool
    {
        return $value === null ? null : (bool) $value;
    }
}