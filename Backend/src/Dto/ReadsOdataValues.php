<?php

declare(strict_types=1);

namespace App\Dto;

use DateTimeImmutable;

/**
 * Typed readers for the untyped values in a raw OData record.
 */
trait ReadsOdataValues
{
    private static function toString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    private static function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function toBool(mixed $value): ?bool
    {
        return $value === null ? null : (bool) $value;
    }

    private static function toDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }
}
