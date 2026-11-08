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


    public function withSkip(): bool;

    /**
     * @return class-string<DtoInterface>
     */
    public function dtoClass(): string;
}
