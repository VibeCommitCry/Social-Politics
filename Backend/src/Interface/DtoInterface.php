<?php

namespace App\Interface;


interface DtoInterface
{
    public static function fromArray(array $data): DtoInterface;

}