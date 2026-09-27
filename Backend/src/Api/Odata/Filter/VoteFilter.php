<?php

namespace App\Api\Odata\Filter;

use App\Enum\VoteField;
use App\Interface\FilterInterface;

final class VoteFilter implements FilterInterface
{
    private readonly string $endpoint;

    /**
     * @param string[] $decisionIds
     */
    public function __construct(
        public readonly int $top = 250,
        public readonly bool $includeDeleted = false,
        public readonly bool $withSkip = false,
        private readonly array $decisionIds = [],
    ) {
        $this->endpoint = VoteField::Endpoint->value;
    }

    public function endpoint(): string
    {
        return $this->endpoint;
    }

    public function toQueryParams(): array
    {
        $params = [
            '$top' => $this->top,
            '$orderby' => 'GewijzigdOp desc',
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
            $conditions[] = VoteField::Deleted->value . ' eq false';
        }

        if ($this->decisionIds !== []) {
            $conditions[] = sprintf(
                '%s in (%s)',
                VoteField::DecisionId->value,
                implode(',', $this->decisionIds)
            );
        }

        return $conditions;
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }
}