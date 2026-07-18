<?php

namespace App\Api\Odata\Interface;


interface FilterInterface
{
    public function toQueryParams(): array;
    public function endpoint(): string;
    public function buildConditions(): array;

}