<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Enum\ActivityField;
use App\Interface\FilterInterface;

final class ActivityFilter implements FilterInterface
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
        return ActivityField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        $params = [
            '$top' => $this->top,
            '$orderby' => ActivityField::Id->value . ' asc',
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
            $conditions[] = ActivityField::Deleted->value . ' eq false';
        }

        if ($this->room !== null) {
            $conditions[] = sprintf(
                "%s eq '%s'",
                ActivityField::Room->value,
                str_replace("'", "''", $this->room)
            );
        }

        if ($this->ids !== []) {
            $conditions[] = sprintf('%s in (%s)', ActivityField::Id->value, implode(',', $this->ids));
        }

        return $conditions;
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }
}
