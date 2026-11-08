<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Dto\DecisionDto;
use App\Enum\DecisionField;
use App\Interface\FilterInterface;

/**
 * @implements FilterInterface<DecisionDto>
 */
final class DecisionFilter implements FilterInterface
{
    public function __construct(
        private readonly int $top = 250,
        private readonly bool $includeDeleted = false,
        private readonly ?string $agendaItemId = null,
        public readonly bool $withSkip = false,
        /** @var string[] */
        private readonly array $agendaItemIds = [],
    ) {}

    public function endpoint(): string
    {
        return DecisionField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        return new ODataQueryBuilder()
            ->top($this->top)
            ->orderByDesc(DecisionField::ModifiedOn)
            ->equalsBool(DecisionField::Deleted, $this->includeDeleted ? null : false)
            ->equalsGuid(DecisionField::AgendaItemId, $this->agendaItemId)
            ->inGuids(DecisionField::AgendaItemId, $this->agendaItemIds)
            ->build();
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }

    public function dtoClass(): string
    {
        return DecisionDto::class;
    }
}
