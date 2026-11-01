<?php

declare(strict_types=1);

namespace App\Api\Odata\Filter;

use BackedEnum;
use DateTimeImmutable;

final class ODataConditions
{
    /** @var list<string> */
    private array $conditions = [];

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

    public function equalsStringVia(BackedEnum $navigation, BackedEnum $field, ?string $value): self
    {
        if ($value !== null) {
            $this->conditions[] = sprintf(
                "%s/%s eq '%s'",
                $navigation->value,
                $field->value,
                str_replace("'", "''", $value)
            );
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

    public function onOrAfter(BackedEnum $field, ?DateTimeImmutable $value): self
    {
        if ($value !== null) {
            $utc = $value->setTimezone(new \DateTimeZone('UTC'));
            $this->conditions[] = sprintf('%s ge %s', $field->value, $utc->format('Y-m-d\TH:i:s\Z'));
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function build(): array
    {
        return $this->conditions;
    }
}
