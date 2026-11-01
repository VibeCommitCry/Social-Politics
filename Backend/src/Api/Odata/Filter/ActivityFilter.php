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
        private readonly ?\DateTimeImmutable $since = null,
    ) {
    }

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
        return new ODataConditions()
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
}
