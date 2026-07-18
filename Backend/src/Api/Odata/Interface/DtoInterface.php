<?php

namespace App\Api\Odata\Interface;


interface DtoInterface
{
    public static function fromArray(array $data): DtoInterface;

}