<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use BackedEnum;
use DateTimeImmutable;

final class ODataQueryBuilder
{
    /** @var list<string> */
    private array $conditions = [];
    /** @var array<string, mixed> */
    private array $params = [];

    public function top(int $top): self
    {
        $this->params['$top'] = $top;
        return $this;
    }

    public function orderByAsc(BackedEnum $field): self
    {
        $this->params['$orderby'] = $field->value . ' asc';
        return $this;
    }

    public function equalsBool(BackedEnum $field, ?bool $value): self
    {
        if ($value !== null) {
            $this->conditions[] = sprintf('%s eq %s', $field->value, $value ? 'true' : 'false');
        }
        return $this;
    }

    public function equalsString(BackedEnum $field, ?string $value): self
    {
        if ($value !== null) {
            $this->conditions[] = sprintf("%s eq '%s'", $field->value, str_replace("'", "''", $value));
        }
        return $this;
    }

    public function inGuids(BackedEnum $field, array $ids): self
    {
        if ($ids !== []) {
            $this->conditions[] = sprintf('%s in (%s)', $field->value, implode(',', $ids));
        }
        return $this;
    }

    public function onOrAfter(BackedEnum $field, ?DateTimeImmutable $value): self
    {
        if ($value !== null) {
            $utc = $value->setTimezone(new \DateTimeZone('UTC'));
            $this->conditions[] = sprintf('%s ge %s', $field->value, $utc->format('Y-m-d\TH:i:s\Z'));
        }
        return $this;
    }

    /**
     * Build the entire array ready for the Symfony HttpClient query option
     * 
     * @return array<string, mixed>
     */
    public function build(): array
    {
        if ($this->conditions !== []) {
            $this->params['$filter'] = implode(' and ', $this->conditions);
        }

        return $this->params;
    }
}
