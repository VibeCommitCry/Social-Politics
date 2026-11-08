<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Dto\FractionDto;
use App\Enum\FractionField;
use App\Interface\FilterInterface;

final class FractionFilter implements FilterInterface
{
    public function __construct(
        public readonly int $top = 5,
        public readonly string $orderDirection = 'desc',
        public readonly FractionField $orderBy = FractionField::SeatCount,
        public readonly bool $includeNamelessFraction = false,
        public readonly bool $includeDeleted = false,
        public readonly bool $withSkip = false,
        public readonly ?string $searchByName = null,
    ) {
    }

    public function endpoint(): string
    {
        return FractionField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        return (new ODataQueryBuilder())
            ->top($this->top)
            ->orderBy($this->orderBy, $this->orderDirection)
            ->equalsBool(FractionField::Deleted, $this->includeDeleted ? null : false)
            ->isNotNull(FractionField::DutchName, !$this->includeNamelessFraction)
            ->equalsString(FractionField::Abbreviation, $this->searchByName ?: null)
            ->build();
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }

    public function dtoClass(): string
    {
        return FractionDto::class;
    }
}
