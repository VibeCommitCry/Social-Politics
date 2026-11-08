<?php

declare(strict_types=1);

namespace App\Interface;

/**
 * @template-covariant T of DtoInterface
 */
interface FilterInterface
{
    /**
     * @return array<string, int|string>
     */
    public function toQueryParams(): array;

    public function endpoint(): string;


    public function withSkip(): bool;

    /**
     * @return class-string<T>
     */
    public function dtoClass(): string;
}
