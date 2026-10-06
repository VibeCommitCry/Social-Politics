<?php

declare(strict_types=1);

namespace App\Interface;

interface FilterInterface
{
    public function toQueryParams(): array;
    public function endpoint(): string;
    public function buildConditions(): array;

    public function withSkip(): bool;
}
