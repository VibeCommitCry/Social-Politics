<?php

namespace App\Api\Dto;

use App\Api\Enum\DocumentField;

final class DocumentDto
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $type,
        public readonly ?string $documentNumber,
        public readonly ?string $titel,
        public readonly ?string $subject,
        public readonly ?string $discussionYear,
        public readonly ?string $room,
        public readonly ?int $serialNumber,
        public readonly ?string $citeTitel,
        public readonly ?string $alias,
        public readonly ?\DateTimeImmutable $dateRegistration,
        public readonly ?\DateTimeImmutable $dateOfReceipt,
        public readonly ?string $appendixNumber,
        public readonly ?string $senderReference,
        public readonly ?string $organisation,
        public readonly ?string $contentType,
        public readonly ?int $contentLength,
        public readonly \DateTimeImmutable $changedOn,
        public readonly \DateTimeImmutable $apiChangedOn,
        public readonly bool $deleted,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data[DocumentField::Id->value],
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
                ? new \DateTimeImmutable($data[DocumentField::DateRegistration->value])
                : null,
            dateOfReceipt: isset($data[DocumentField::DateOfReceipt->value])
                ? new \DateTimeImmutable($data[DocumentField::DateOfReceipt->value])
                : null,
            appendixNumber: $data[DocumentField::AppendixNumber->value] ?? null,
            senderReference: $data[DocumentField::SenderReference->value] ?? null,
            organisation: $data[DocumentField::Organisation->value] ?? null,
            contentType: $data[DocumentField::ContentType->value] ?? null,
            contentLength: isset($data[DocumentField::ContentLength->value])
                ? (int) $data[DocumentField::ContentLength->value]
                : null,
            changedOn: new \DateTimeImmutable($data[DocumentField::ChangedOn->value]),
            apiChangedOn: new \DateTimeImmutable($data[DocumentField::ApiChangedOn->value]),
            deleted: (bool) $data[DocumentField::Deleted->value],
        );
    }
}