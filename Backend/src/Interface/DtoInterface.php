<?php

declare(strict_types=1);

namespace App\Interface;

interface DtoInterface
{
    public static function fromArray(array $data): DtoInterface;

}
