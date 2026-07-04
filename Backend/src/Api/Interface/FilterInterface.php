<?php

namespace App\Api\Interface;


interface FilterInterface
{
    public function toQueryParams(): array;
    public function endpoint(): string;
}