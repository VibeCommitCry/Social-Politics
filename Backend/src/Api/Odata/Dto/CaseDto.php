<?php

namespace App\Api\Odata\Dto;

use App\Api\Odata\Enum\CaseField;
use App\Api\Odata\Interface\DtoInterface;
use DateTimeImmutable;

final class CaseDto implements DtoInterface
{
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
        public readonly ?string $currentStatus = null, // NEW
        public readonly ?bool $completed = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
        public readonly ?string $cabinetAppreciation = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data[CaseField::Id->value] ?? null,
            number: $data[CaseField::Number->value] ?? null,
            type: $data[CaseField::Type->value] ?? null,
            title: $data[CaseField::Title->value] ?? null,
            citeTitle: $data[CaseField::CiteTitle->value] ?? null,
            alias: $data[CaseField::Alias->value] ?? null,
            subject: $data[CaseField::Subject->value] ?? null,
            date: self::toDate($data[CaseField::Date->value] ?? null),
            room: $data[CaseField::Room->value] ?? null,
            advanceNotice: $data[CaseField::AdvanceNotice->value] ?? null,
            deadline: self::toDate($data[CaseField::Deadline->value] ?? null),
            meetingYear: $data[CaseField::MeetingYear->value] ?? null,
            subNumber: $data[CaseField::SubNumber->value] ?? null,
            currentStatus: $data[CaseField::CurrentStatus->value] ?? null, // NEW
            completed: self::toBool($data[CaseField::Completed->value] ?? null),
            modifiedOn: self::toDate($data[CaseField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[CaseField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[CaseField::Deleted->value] ?? null),
            cabinetAppreciation: $data[CaseField::CabinetAppreciation->value] ?? null,
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
}