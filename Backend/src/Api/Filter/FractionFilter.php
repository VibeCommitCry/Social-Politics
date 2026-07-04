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
        public readonly bool $includeDeleted = false,
    ) {
        $this->endpoint = 'fractie';
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

        if (!$this->includeDeleted) {
            $params['$filter'] = 'Verwijderd eq false';
        }

        return $params;
    }
}