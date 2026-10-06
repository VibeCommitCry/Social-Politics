<?php

declare(strict_types=1);

namespace App\Interface;

interface DtoInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): DtoInterface;

}
