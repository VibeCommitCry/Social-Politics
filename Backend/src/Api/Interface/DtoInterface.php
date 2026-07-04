<?php

namespace App\Api\Interface;


interface DtoInterface
{
    public static function fromArray(array $data): DtoInterface;

}