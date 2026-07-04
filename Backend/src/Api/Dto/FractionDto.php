<?php

namespace App\Api\Dto;

use App\Api\Interface\DtoInterface;
use DateTimeImmutable;

final class FractionDto implements DtoInterface
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?int $nummer,
        public readonly ?string $afkorting,
        public readonly ?string $naamNL,
        public readonly ?string $naamEN,
        public readonly ?int $aantalZetels,
        public readonly ?int $aantalStemmen,
        public readonly ?DateTimeImmutable $datumActief,
        public readonly ?DateTimeImmutable $datumInactief,
        public readonly ?string $contentType,
        public readonly ?int $contentLength,
        public readonly ?DateTimeImmutable $gewijzigdOp,
        public readonly ?DateTimeImmutable $apiGewijzigdOp,
        public readonly ?bool $verwijderd,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['Id'],
            nummer: $data['Nummer'],
            afkorting: $data['Afkorting'],
            naamNL: $data['NaamNL'],
            naamEN: $data['NaamEN'] ?? null,
            aantalZetels: $data['AantalZetels'],
            aantalStemmen: $data['AantalStemmen'],
            datumActief: new DateTimeImmutable($data['DatumActief']),
            datumInactief: isset($data['DatumInactief']) ? new DateTimeImmutable($data['DatumInactief']) : null,
            contentType: $data['ContentType'] ?? null,
            contentLength: $data['ContentLength'] ?? null,
            gewijzigdOp: new DateTimeImmutable($data['GewijzigdOp']),
            apiGewijzigdOp: new DateTimeImmutable($data['ApiGewijzigdOp']),
            verwijderd: (bool) $data['Verwijderd'],
        );
    }
}