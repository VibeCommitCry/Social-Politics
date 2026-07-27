<?php

namespace App\Api\Odata\Filter;

use App\Enum\DecisionField;
use App\Interface\FilterInterface;

final class DecisionFilter implements FilterInterface
{
    public function __construct(
        private readonly int $top = 250,
        private readonly bool $includeDeleted = false,
        private readonly ?string $agendaItemId = null,
    ) {
    }

    public function buildConditions(): array
    {
        $conditions = [];

        if (!$this->includeDeleted) {
            $conditions[] = 'Verwijderd eq false';
        }

        if ($this->agendaItemId !== null) {
            $conditions[] = sprintf("Agendapunt_Id eq %s", $this->agendaItemId);
        }

        return $conditions;
    }

    public function toQueryParams(): array
    {
        $query = ['$top' => $this->top];

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
        return true;
    }
}