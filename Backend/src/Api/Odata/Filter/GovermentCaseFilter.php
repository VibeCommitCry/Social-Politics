<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Dto\GovermentCaseDto;
use App\Enum\DecisionField;
use App\Enum\GovermentCaseField;
use App\Interface\FilterInterface;

final class GovermentCaseFilter implements FilterInterface
{
    public function __construct(
        public readonly int $top = 5,
        public readonly ?string $orderBy = 'Datum',
        public readonly string $orderDirection = 'desc',
        public readonly bool $includeDeleted = false,
        public readonly ?string $type = null,
        public readonly ?string $room = null,
        public readonly ?bool $completed = null,
        public readonly bool $withSkip = false,
        public readonly bool $expandDecisions = false,
    ) {
    }

    public function endpoint(): string
    {
        return GovermentCaseField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        return (new ODataQueryBuilder())
            ->top($this->top)
            ->orderBy($this->orderBy, $this->orderDirection)
            ->expand(DecisionField::Endpoint, $this->expandDecisions)
            ->equalsBool(GovermentCaseField::Deleted, $this->includeDeleted ? null : false)
            ->equalsString(GovermentCaseField::Type, $this->type)
            ->equalsString(GovermentCaseField::Room, $this->room)
            ->equalsBool(GovermentCaseField::Completed, $this->completed)
            ->build();
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }

    public function dtoClass(): string
    {
        return GovermentCaseDto::class;
    }
}
