<?php

declare(strict_types=1);

namespace App\Interface;

interface FilterInterface
{
    /**
     * @return array<string, int|string>
     */
    public function toQueryParams(): array;

    public function endpoint(): string;

    /**
     * @return list<string>
     */
    public function buildConditions(): array;

    public function withSkip(): bool;
}
