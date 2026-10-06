<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\GovermentCaseField;
use App\Interface\DtoInterface;
use DateTimeImmutable;

final class GovermentCaseDto implements DtoInterface
{
    /**
     * @param list<DecisionDto>|null $decisions
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $number = null,
        public readonly ?string $type = null,
        public readonly ?string $title = null,
        public readonly ?string $citeTitle = null,
        public readonly ?string $alias = null,
        public readonly ?string $subject = null,
        public readonly ?DateTimeImmutable $date = null,
        public readonly ?string $room = null,
        public readonly ?string $advanceNotice = null,
        public readonly ?DateTimeImmutable $deadline = null,
        public readonly ?string $meetingYear = null,
        public readonly ?int $subNumber = null,
        public readonly ?string $currentStatus = null,
        public readonly ?bool $completed = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
        public readonly ?string $cabinetAppreciation = null,
        public readonly ?array $decisions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data[GovermentCaseField::Id->value] ?? null,
            number: $data[GovermentCaseField::Number->value] ?? null,
            type: $data[GovermentCaseField::Type->value] ?? null,
            title: $data[GovermentCaseField::Title->value] ?? null,
            citeTitle: $data[GovermentCaseField::CiteTitle->value] ?? null,
            alias: $data[GovermentCaseField::Alias->value] ?? null,
            subject: $data[GovermentCaseField::Subject->value] ?? null,
            date: self::toDate($data[GovermentCaseField::Date->value] ?? null),
            room: $data[GovermentCaseField::Room->value] ?? null,
            advanceNotice: $data[GovermentCaseField::AdvanceNotice->value] ?? null,
            deadline: self::toDate($data[GovermentCaseField::Deadline->value] ?? null),
            meetingYear: $data[GovermentCaseField::MeetingYear->value] ?? null,
            subNumber: $data[GovermentCaseField::SubNumber->value] ?? null,
            currentStatus: $data[GovermentCaseField::CurrentStatus->value] ?? null,
            completed: self::toBool($data[GovermentCaseField::Completed->value] ?? null),
            modifiedOn: self::toDate($data[GovermentCaseField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[GovermentCaseField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[GovermentCaseField::Deleted->value] ?? null),
            cabinetAppreciation: $data[GovermentCaseField::CabinetAppreciation->value] ?? null,
            decisions: isset($data[GovermentCaseField::DecisionArray->value])
                ? self::makeDecisionDtoArray($data[GovermentCaseField::DecisionArray->value])
                : null,
        );
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

    /**
     * @param list<array<string, mixed>> $decisionArray
     *
     * @return list<DecisionDto>
     */
    private static function makeDecisionDtoArray(array $decisionArray): array
    {
        $array = [];

        foreach ($decisionArray as $decision) {
            array_push($array, DecisionDto::fromArray($decision));
        }
        return $array;
    }
}
