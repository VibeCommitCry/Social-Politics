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
        private readonly ?\DateTimeImmutable $since = null,

        /** @var string[] */
        private readonly array $ids = [],
    ) {
    }

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
        return new ODataConditions()
            ->equalsBool(AgendaPointField::Deleted, $this->includeDeleted ? null : false)
            ->equalsStringVia(AgendaPointField::Activity, ActivityField::Room, $this->room)
            ->inGuids(AgendaPointField::Id, $this->ids)
            ->build();
    }

    public function withSkip(): bool
    {
        return $this->withSkip;
    }
}
