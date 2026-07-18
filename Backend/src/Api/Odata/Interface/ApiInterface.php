<?php

namespace App\Api\Odata\Interface;


interface ApiInterface
{
    public function search(FilterInterface $filter, string $dto): array;

}