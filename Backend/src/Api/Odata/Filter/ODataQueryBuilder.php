<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use BackedEnum;
use DateTimeImmutable;

/**
 * Builds OData query params. Quoting rules (verified against the live API):
 * Edm.String values are quoted with ' escaped as '', Edm.Guid values are unquoted.
 */
final class ODataQueryBuilder
{
    /** @var list<string> */
    private array $conditions = [];
    /** @var array<string, int|string> */
    private array $params = [];

    public function top(int $top): self
    {
        $this->params['$top'] = $top;
        return $this;
    }

    /**
     * @param BackedEnum|string|null $field null leaves $orderby unset
     */
    public function orderBy(BackedEnum|string|null $field, string $direction = 'asc'): self
    {
        if ($field !== null) {
            $name = $field instanceof BackedEnum ? (string) $field->value : $field;
            $this->params['$orderby'] = sprintf('%s %s', $name, $direction);
        }
        return $this;
    }

    public function orderByAsc(BackedEnum $field): self
    {
        return $this->orderBy($field, 'asc');
    }

    public function orderByDesc(BackedEnum $field): self
    {
        return $this->orderBy($field, 'desc');
    }

    public function expand(BackedEnum $navigation, bool $when = true): self
    {
        if ($when) {
            $this->params['$expand'] = (string) $navigation->value;
        }
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
            $this->conditions[] = sprintf('%s eq %s', $field->value, self::quote($value));
        }
        return $this;
    }

    public function equalsStringVia(BackedEnum $navigation, BackedEnum $field, ?string $value): self
    {
        if ($value !== null) {
            $this->conditions[] = sprintf('%s/%s eq %s', $navigation->value, $field->value, self::quote($value));
        }
        return $this;
    }

    public function equalsGuid(BackedEnum $field, ?string $value): self
    {
        if ($value !== null) {
            $this->conditions[] = sprintf('%s eq %s', $field->value, $value);
        }
        return $this;
    }

    /**
     * @param string[] $ids
     */
    public function inGuids(BackedEnum $field, array $ids): self
    {
        if ($ids !== []) {
            $this->conditions[] = sprintf('%s in (%s)', $field->value, implode(',', $ids));
        }
        return $this;
    }

    public function isNotNull(BackedEnum $field, bool $when = true): self
    {
        if ($when) {
            $this->conditions[] = sprintf('%s ne null', $field->value);
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
     * Build the entire array ready for the Symfony HttpClient query option.
     *
     * @return array<string, int|string>
     */
    public function build(): array
    {
        $params = $this->params;

        if ($this->conditions !== []) {
            $params['$filter'] = implode(' and ', $this->conditions);
        }

        return $params;
    }

    private static function quote(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
}
