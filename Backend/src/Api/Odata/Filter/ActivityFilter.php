<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use App\Dto\ActivityDto;
use App\Enum\ActivityField;
use App\Interface\FilterInterface;

final class ActivityFilter implements FilterInterface
{
    public function __construct(
        public readonly int $top = 250,
        public readonly bool $includeDeleted = false,
        public readonly bool $withSkip = false,
        public readonly ?string $room = null,
        private readonly ?\DateTimeImmutable $since = null,
        /** @var string[] */
        private readonly array $ids = [],
    ) {
    }

    public function endpoint(): string
    {
        return ActivityField::Endpoint->value;
    }

    public function toQueryParams(): array
    {
        return (new ODataQueryBuilder())
            ->top($this->top)
            ->orderByAsc(ActivityField::Date)
            ->onOrAfter(ActivityField::Date, $this->since)
            ->equalsBool(ActivityField::Deleted, $this->includeDeleted ? null : false)
            ->equalsString(ActivityField::Room, $this->room)
            ->inGuids(ActivityField::Id, $this->ids)
            ->build();
    }
    public function withSkip(): bool
    {
        return $this->withSkip;
    }

    public function dtoClass(): string
    {
        return ActivityDto::class;
    }
}
