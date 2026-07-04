<?php

namespace App\Api\Interface;


interface ApiInterface
{
    public function search(FilterInterface $filter, string $dto): array;

}