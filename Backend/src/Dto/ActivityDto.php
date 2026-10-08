<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\ActivityField;
use App\Interface\DtoInterface;
use DateTimeImmutable;

final class ActivityDto implements DtoInterface
{
    use ReadsOdataValues;

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $type = null,
        public readonly ?string $number = null,
        public readonly ?string $subject = null,
        public readonly ?string $dateType = null,
        public readonly ?DateTimeImmutable $date = null,
        public readonly ?DateTimeImmutable $startTime = null,
        public readonly ?DateTimeImmutable $endTime = null,
        public readonly ?string $location = null,
        public readonly ?bool $private = null,
        public readonly ?string $status = null,
        public readonly ?string $parliamentaryYear = null,
        public readonly ?string $room = null,
        public readonly ?string $remark = null,
        public readonly ?string $leadCommitteeName = null,
        public readonly ?string $leadCommitteeAbbreviation = null,
        public readonly ?string $leadCommitteeShortName = null,
        public readonly ?string $leadCommitteeId = null,
        public readonly ?DateTimeImmutable $requestDate = null,
        public readonly ?DateTimeImmutable $firstExtensionRequestDate = null,
        public readonly ?DateTimeImmutable $firstExtensionNoticeDate = null,
        public readonly ?DateTimeImmutable $secondExtensionRequestDate = null,
        public readonly ?DateTimeImmutable $secondExtensionNoticeDate = null,
        public readonly ?DateTimeImmutable $expiryDate = null,
        public readonly ?DateTimeImmutable $modifiedOn = null,
        public readonly ?DateTimeImmutable $apiModifiedOn = null,
        public readonly ?bool $deleted = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::toString($data[ActivityField::Id->value] ?? null),
            type: self::toString($data[ActivityField::Type->value] ?? null),
            number: self::toString($data[ActivityField::Number->value] ?? null),
            subject: self::toString($data[ActivityField::Subject->value] ?? null),
            dateType: self::toString($data[ActivityField::DateType->value] ?? null),
            date: self::toDate($data[ActivityField::Date->value] ?? null)?->setTimezone(new \DateTimeZone('Europe/Amsterdam')),
            startTime: self::toDate($data[ActivityField::StartTime->value] ?? null),
            endTime: self::toDate($data[ActivityField::EndTime->value] ?? null),
            location: self::toString($data[ActivityField::Location->value] ?? null),
            private: self::toBool($data[ActivityField::Private->value] ?? null),
            status: self::toString($data[ActivityField::Status->value] ?? null),
            parliamentaryYear: self::toString($data[ActivityField::ParliamentaryYear->value] ?? null),
            room: self::toString($data[ActivityField::Room->value] ?? null),
            remark: self::toString($data[ActivityField::Remark->value] ?? null),
            leadCommitteeName: self::toString($data[ActivityField::LeadCommitteeName->value] ?? null),
            leadCommitteeAbbreviation: self::toString($data[ActivityField::LeadCommitteeAbbreviation->value] ?? null),
            leadCommitteeShortName: self::toString($data[ActivityField::LeadCommitteeShortName->value] ?? null),
            leadCommitteeId: self::toString($data[ActivityField::LeadCommitteeId->value] ?? null),
            requestDate: self::toDate($data[ActivityField::RequestDate->value] ?? null),
            firstExtensionRequestDate: self::toDate($data[ActivityField::FirstExtensionRequestDate->value] ?? null),
            firstExtensionNoticeDate: self::toDate($data[ActivityField::FirstExtensionNoticeDate->value] ?? null),
            secondExtensionRequestDate: self::toDate($data[ActivityField::SecondExtensionRequestDate->value] ?? null),
            secondExtensionNoticeDate: self::toDate($data[ActivityField::SecondExtensionNoticeDate->value] ?? null),
            expiryDate: self::toDate($data[ActivityField::ExpiryDate->value] ?? null),
            modifiedOn: self::toDate($data[ActivityField::ModifiedOn->value] ?? null),
            apiModifiedOn: self::toDate($data[ActivityField::ApiModifiedOn->value] ?? null),
            deleted: self::toBool($data[ActivityField::Deleted->value] ?? null),
        );
    }
}
