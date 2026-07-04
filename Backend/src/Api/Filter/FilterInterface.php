<?php

namespace App\Api\Filter;


interface FilterInterface
{
    public function toQueryParams(): array;
}