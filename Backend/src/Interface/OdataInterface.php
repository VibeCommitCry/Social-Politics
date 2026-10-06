<?php

declare(strict_types=1);

namespace App\Interface;

use Generator;

interface OdataInterface
{
    public function search(FilterInterface $filter, string $dto): Generator;

}
