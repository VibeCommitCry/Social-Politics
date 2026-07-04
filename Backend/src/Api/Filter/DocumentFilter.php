<?php

namespace App\Api\Filter;

final class DocumentFilter implements FilterInterface
{
    public function __construct(
        public readonly int $top = 5,
        public readonly bool $includeDeleted = false,
        public readonly ?string $orderBy = 'Datum',
        public readonly string $orderDirection = 'desc',
        public readonly ?string $room = null,
        public readonly ?string $type = null,
    ) {}

    public function toQueryParams(): array
    {
        $filters = [];

        if (!$this->includeDeleted) {
            $filters[] = 'Verwijderd eq false';
        }

        if ($this->room !== null) {
            $filters[] = sprintf("Kamer eq '%s'", $this->room);
        }

        if ($this->type !== null) {
            $filters[] = sprintf("Soort eq '%s'", $this->type);
        }

        $params = [
            '$top' => $this->top,
        ];

        if (!empty($filters)) {
            $params['$filter'] = implode(' and ', $filters);
        }

        if ($this->orderBy !== null) {
            $params['$orderby'] = sprintf('%s %s', $this->orderBy, $this->orderDirection);
        }
        return $params;
    }
}