<?php

namespace App\Dto;

use App\Interface\DtoInterface;
use App\Enum\DecisionField;
use App\Enum\DecisionVoteMethod;
use App\Enum\DecisionStatus;
use DateTimeImmutable;

final class DecisionDto implements DtoInterface
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $agendaItemId = null,
        public readonly ?DecisionVoteMethod $voteMethod = null,
        public readonly ?string $type = null,
        public readonly ?string $text = null,
        public readonly ?string $remark = null,
        public readonly ?DecisionStatus $status = null,
        public readonly ?int $sequence = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data[DecisionField::Id->value] ?? null,
            agendaItemId: $data[DecisionField::AgendaItemId->value] ?? null,
            voteMethod: self::toVoteMethod($data[DecisionField::VoteMethod->value] ?? null),
            type: $data[DecisionField::Type->value] ?? null,
            text: $data[DecisionField::Text->value] ?? null,
            remark: $data[DecisionField::Remark->value] ?? null,
            status: self::toStatus($data[DecisionField::Status->value] ?? null),
            sequence: $data[DecisionField::Sequence->value] ?? null,
            modifiedOn: self::toDate($data[DecisionField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[DecisionField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[DecisionField::Deleted->value] ?? null),
        );
    }

    private static function toVoteMethod(?string $value): ?DecisionVoteMethod
    {
        if ($value === null || $value === '') {
            return null;
        }

        return DecisionVoteMethod::tryFrom($value);
    }

    private static function toStatus(?string $value): ?DecisionStatus
    {
        if ($value === null || $value === '') {
            return null;
        }

        return DecisionStatus::tryFrom($value);
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