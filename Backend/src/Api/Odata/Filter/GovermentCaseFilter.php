<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Enum\DecisionField;
use App\Enum\GovermentCaseField;
use App\Interface\FilterInterface;

final class GovermentCaseFilter implements FilterInterface
{
    private readonly string $endpoint;

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
        $this->endpoint = GovermentCaseField::Endpoint->value;
    }

    public function endpoint(): string
    {
        return $this->endpoint;
    }

    public function toQueryParams(): array
    {
        $params = [
            '$top' => $this->top,
        ];

        $conditions = $this->buildConditions();

        if ($conditions !== []) {
            $params['$filter'] = implode(' and ', $conditions);
        }

        if ($this->orderBy !== null) {
            $params['$orderby'] = sprintf('%s %s', $this->orderBy, $this->orderDirection);
        }

        if ($this->expandDecisions) {
            $params['$expand'] = DecisionField::Endpoint->value;
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
            $conditions[] = GovermentCaseField::Deleted->value . ' eq false';
        }

        if ($this->type !== null) {
            $conditions[] = sprintf(
                "%s eq '%s'",
                GovermentCaseField::Type->value,
                str_replace("'", "''", $this->type)
            );
        }

        if ($this->room !== null) {
            $conditions[] = sprintf(
                "%s eq '%s'",
                GovermentCaseField::Room->value,
                str_replace("'", "''", $this->room)
            );
        }

        if ($this->completed !== null) {
            $conditions[] = sprintf(
                '%s eq %s',
                GovermentCaseField::Completed->value,
                $this->completed ? 'true' : 'false'
            );
        }

        return $conditions;
    }
    public function withSkip(): bool
    {
        return $this->withSkip;
    }
}
