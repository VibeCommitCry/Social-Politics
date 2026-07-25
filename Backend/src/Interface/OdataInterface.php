<?php

namespace App\Interface;

use Generator;


interface OdataInterface
{
    public function search(FilterInterface $filter, string $dto): Generator;

}