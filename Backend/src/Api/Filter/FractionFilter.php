<?php

namespace App\Api\Filter;

use App\Api\Enum\FractionField;
use App\Api\Interface\FilterInterface;

final class FractionFilter implements FilterInterface
{
    private readonly string $endpoint;

    public function __construct(
        public readonly int $top = 5,
        public readonly string $orderDirection = 'desc',
        public readonly FractionField $orderBy = FractionField::SeatCount,
        public readonly bool $includeNamelessFraction = false,
        public readonly bool $includeInactiveFraction = false,
        public readonly bool $includeDeleted = false,
        public readonly ?string $searchByName = null,
    ) {
        $this->endpoint = FractionField::Endpoint->value;
    }

    public function endpoint(): string
    {
        return $this->endpoint;
    }

    public function toQueryParams(): array
    {
        $params = [
            '$top' => $this->top,
            '$orderby' => sprintf('%s %s', $this->orderBy->value, $this->orderDirection),
        ];

        $conditions = $this->buildConditions();

        if ($conditions !== []) {
            $params['$filter'] = implode(' and ', $conditions);
        }

        return $params;
    }

    /**
     * @return string[]
     */
    public function buildConditions(): array
    {
        $conditions = [];

        if (!$this->includeDeleted) {
            $conditions[] = FractionField::Deleted->value . ' eq false';
        }

        if (!$this->includeNamelessFraction) {
            $conditions[] = FractionField::DutchName->value . ' ne null';
        }

        if (!$this->includeInactiveFraction) {
            $conditions[] = FractionField::ActiveUntil->value . ' eq null';
        }

        if ($this->searchByName) {
            $conditions[] = sprintf(
                "%s eq '%s'",
                FractionField::Abbreviation->value,
                str_replace("'", "''", $this->searchByName)
            );
        }

        return $conditions;
    }
}