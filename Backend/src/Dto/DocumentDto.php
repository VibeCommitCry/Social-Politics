<?php

namespace App\Dto;

use App\Interface\DtoInterface;
use App\Enum\DocumentField;

use DateTimeImmutable;

final class DocumentDto implements DtoInterface
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $type = null,
        public readonly ?string $documentNumber = null,
        public readonly ?string $titel = null,
        public readonly ?string $subject = null,
        public readonly ?string $discussionYear = null,
        public readonly ?string $room = null,
        public readonly ?int $serialNumber = null,
        public readonly ?string $citeTitel = null,
        public readonly ?string $alias = null,
        public readonly ?DateTimeImmutable $dateRegistration = null,
        public readonly ?DateTimeImmutable $dateOfReceipt = null,
        public readonly ?string $appendixNumber = null,
        public readonly ?string $senderReference = null,
        public readonly ?string $organisation = null,
        public readonly ?string $contentType = null,
        public readonly ?int $contentLength = null,
        public readonly ?DateTimeImmutable $changedOn = null,
        public readonly ?DateTimeImmutable $apiChangedOn = null,
        public readonly ?bool $deleted = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data[DocumentField::Id->value] ?? null,
            type: $data[DocumentField::Type->value] ?? null,
            documentNumber: $data[DocumentField::DocumentNumber->value] ?? null,
            titel: $data[DocumentField::Titel->value] ?? null,
            subject: $data[DocumentField::Subject->value] ?? null,
            discussionYear: $data[DocumentField::DiscussionYear->value] ?? null,
            room: $data[DocumentField::Room->value] ?? null,
            serialNumber: isset($data[DocumentField::SerialNumber->value])
            ? (int) $data[DocumentField::SerialNumber->value]
            : null,
            citeTitel: $data[DocumentField::CiteTitel->value] ?? null,
            alias: $data[DocumentField::Alias->value] ?? null,
            dateRegistration: isset($data[DocumentField::DateRegistration->value])
            ? new DateTimeImmutable($data[DocumentField::DateRegistration->value])
            : null,
            dateOfReceipt: isset($data[DocumentField::DateOfReceipt->value])
            ? new DateTimeImmutable($data[DocumentField::DateOfReceipt->value])
            : null,
            appendixNumber: $data[DocumentField::AppendixNumber->value] ?? null,
            senderReference: $data[DocumentField::SenderReference->value] ?? null,
            organisation: $data[DocumentField::Organisation->value] ?? null,
            contentType: $data[DocumentField::ContentType->value] ?? null,
            contentLength: isset($data[DocumentField::ContentLength->value])
            ? (int) $data[DocumentField::ContentLength->value]
            : null,
            changedOn: new DateTimeImmutable($data[DocumentField::ChangedOn->value]),
            apiChangedOn: new DateTimeImmutable($data[DocumentField::ApiChangedOn->value]),
            deleted: (bool) $data[DocumentField::Deleted->value] ?? null,
        );
    }
}