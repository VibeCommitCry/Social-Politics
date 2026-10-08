<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Enum\ActivityField;
use App\Enum\AgendaPointField;
use App\Interface\FilterInterface;

final class AgendaPointFilter implements FilterInterface
{
    public function __construct(
        public readonly int $top = 250,
        public readonly bool $includeDeleted = false,
        public readonly bool $withSkip = false,
        public readonly ?string $room = null,
        /** @var string[] */
        private readonly array $ids = [],
    ) {}

    public function endpoint(): string
    {
        return AgendaPointField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        $params = [
            '$top' => $this->top,
            '$orderby' => AgendaPointField::Id->value . ' asc',
        ];

        $conditions = $this->buildConditions();

        if ($conditions !== []) {
            $params['$filter'] = implode(' and ', $conditions);
        }

        return $params;
    }

    public function buildConditions(): array
    {
        $conditions = [];

        if (!$this->includeDeleted) {
            $conditions[] = AgendaPointField::Deleted->value . ' eq false';
        }

        if ($this->room !== null) {
            $conditions[] = sprintf(
                "%s/%s eq '%s'",
                AgendaPointField::Activity->value,
                ActivityField::Room->value,
                str_replace("'", "''", $this->room)
            );
        }

        if ($this->ids !== []) {
            $conditions[] = sprintf('%s in (%s)', AgendaPointField::Id->value, implode(',', $this->ids));
        }

        return $conditions;
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }
}
