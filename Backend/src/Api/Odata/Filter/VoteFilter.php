<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Dto\VoteDto;
use App\Enum\VoteField;
use App\Interface\FilterInterface;

final class VoteFilter implements FilterInterface
{
    /**
     * @param string[] $decisionIds
     */
    public function __construct(
        public readonly int $top = 250,
        public readonly bool $includeDeleted = false,
        public readonly bool $withSkip = false,
        private readonly array $decisionIds = [],
    ) {
    }

    public function endpoint(): string
    {
        return VoteField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        return (new ODataQueryBuilder())
            ->top($this->top)
            ->orderByDesc(VoteField::ModifiedOn)
            ->equalsBool(VoteField::Deleted, $this->includeDeleted ? null : false)
            ->inGuids(VoteField::DecisionId, $this->decisionIds)
            ->build();
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }

    public function dtoClass(): string
    {
        return VoteDto::class;
    }
}
