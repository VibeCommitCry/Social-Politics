<?php

namespace App\Api\Odata\Interface;

use Generator;


interface ApiInterface
{
    public function search(FilterInterface $filter, string $dto): Generator;

}