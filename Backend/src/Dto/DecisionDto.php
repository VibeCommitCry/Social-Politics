<?php

declare(strict_types=1);

namespace App\Dto;

use App\Interface\DtoInterface;
use App\Enum\DecisionField;
use App\Enum\DecisionStatus;
use DateTimeImmutable;

final class DecisionDto implements DtoInterface
{
    use ReadsOdataValues;

    public function __construct(
        public readonly ?string $id = null,
        public ?string $caseId = null,
        public readonly ?string $agendaItemId = null,
        public readonly ?string $type = null,
        public readonly ?string $text = null,
        public readonly ?string $remark = null,
        public readonly ?DecisionStatus $status = null,
        public readonly ?int $sequence = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
    ) {}

    public static function fromArray(array $data, ?string $caseId = null): self
    {
        return new self(
            id: self::toString($data[DecisionField::Id->value] ?? null),
            caseId: $caseId,
            agendaItemId: self::toString($data[DecisionField::AgendaItemId->value] ?? null),
            type: self::toString($data[DecisionField::Type->value] ?? null),
            text: self::toString($data[DecisionField::Text->value] ?? null),
            remark: self::toString($data[DecisionField::Remark->value] ?? null),
            status: self::toStatus($data[DecisionField::Status->value] ?? null),
            sequence: self::toInt($data[DecisionField::Sequence->value] ?? null),
            modifiedOn: self::toDate($data[DecisionField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[DecisionField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[DecisionField::Deleted->value] ?? null),
        );
    }

    private static function toStatus(mixed $value): ?DecisionStatus
    {
        $value = self::toString($value);

        return $value === null || $value === '' ? null : DecisionStatus::tryFrom($value);
    }
}
