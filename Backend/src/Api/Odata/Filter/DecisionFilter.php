<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Enum\DecisionField;
use App\Interface\FilterInterface;

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

    public function buildConditions(): array
    {
        return new ODataConditions()
            ->equalsBool(DecisionField::Deleted, $this->includeDeleted ? null : false)
            ->equalsGuid(DecisionField::AgendaItemId, $this->agendaItemId)
            ->inGuids(DecisionField::AgendaItemId, $this->agendaItemIds)
            ->build();
    }

    public function toQueryParams(): array
    {
        $query = [
            '$top' => $this->top,
            '$orderby' => DecisionField::ModifiedOn->value .' desc',
        ];

        $conditions = $this->buildConditions();

        if ($conditions !== []) {
            $query['$filter'] = implode(' and ', $conditions);
        }

        return $query;
    }

    public function endpoint(): string
    {
        return DecisionField::Endpoint->value;
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }
}
