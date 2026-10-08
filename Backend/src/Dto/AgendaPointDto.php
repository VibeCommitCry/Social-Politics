<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\AgendaPointField;
use App\Interface\DtoInterface;
use DateTimeImmutable;

final class AgendaPointDto implements DtoInterface
{
    use ReadsOdataValues;

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $number = null,
        public readonly ?string $subject = null,
        public readonly ?DateTimeImmutable $startTime = null,
        public readonly ?DateTimeImmutable $endTime = null,
        public readonly ?int $sequence = null,
        public readonly ?string $heading = null,
        public readonly ?string $remark = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
        public readonly ?string $activityId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::toString($data[AgendaPointField::Id->value] ?? null),
            number: self::toString($data[AgendaPointField::Number->value] ?? null),
            subject: self::toString($data[AgendaPointField::Subject->value] ?? null),
            startTime: self::toDate($data[AgendaPointField::StartTime->value] ?? null),
            endTime: self::toDate($data[AgendaPointField::EndTime->value] ?? null),
            sequence: self::toInt($data[AgendaPointField::Sequence->value] ?? null),
            heading: self::toString($data[AgendaPointField::Heading->value] ?? null),
            remark: self::toString($data[AgendaPointField::Remark->value] ?? null),
            modifiedOn: self::toDate($data[AgendaPointField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[AgendaPointField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[AgendaPointField::Deleted->value] ?? null),
            activityId: self::toString($data[AgendaPointField::ActivityId->value] ?? null),
        );
    }
}
